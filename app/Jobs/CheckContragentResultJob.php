<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ContragentApiService;
use App\Services\ContragentAggregator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckContragentResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $report;
    protected $target;
    protected $idRequest;
    protected $attempt;
    
    public $timeout = 30;
    public $tries = 1;
    
    public function __construct(Report $report, string $target, string $idRequest, int $attempt = 1)
    {
        $this->report = $report;
        $this->target = $target;
        $this->idRequest = $idRequest;
        $this->attempt = $attempt;
    }
    
    public function handle(ContragentApiService $apiService)
    {
        // Логируем начало проверки
        Log::info("🔍 CHECK ATTEMPT {$this->attempt} FOR {$this->target}", [
            'report_id' => $this->report->id,
            'id_request' => $this->idRequest,
            'time' => now()->format('H:i:s')
        ]);
        
        // Запрашиваем результат
        $result = $apiService->getResult($this->target, $this->idRequest);
        
        // Логируем что получили от API
        Log::info("📦 API RESPONSE FOR {$this->target}", [
            'result' => json_encode($result, JSON_UNESCAPED_UNICODE)
        ]);
        
        if ($result === null) {
            Log::error("❌ API RETURNED NULL FOR {$this->target}");
            $this->handleError('Техническая ошибка при запросе');
            return;
        }
        
        // Анализируем статус
        if (isset($result['status'])) {
            Log::info("📊 STATUS FOR {$this->target}: " . $result['status']);
            
            if ($result['status'] === 'ok') {
                Log::info("✅ SUCCESS FOR {$this->target} on attempt {$this->attempt}");
                $this->handleSuccess($result);
                return;
            }
            
            if ($result['status'] === 'error') {
                $errorMsg = $result['message'] ?? 'Неизвестная ошибка';
                Log::error("❌ ERROR FOR {$this->target}: " . $errorMsg);
                $this->handleError($errorMsg);
                return;
            }
            
            if ($result['status'] === 'wait') {
                Log::info("⏳ WAITING FOR {$this->target}, attempt {$this->attempt}/" . config('contragent.max_polling_attempts'));
                $this->scheduleNextCheck();
                return;
            }
        }
        
        Log::warning("⚠️ UNKNOWN RESPONSE FOR {$this->target}, scheduling next check");
        $this->scheduleNextCheck();
    }
    
    protected function handleSuccess(array $result)
    {
        // Проверяем, есть ли в ответе поле response с JSON строкой
        $dataToSave = $result;
        
        if (isset($result['response']) && is_string($result['response'])) {
            // Пробуем распарсить JSON строку
            $decodedResponse = json_decode($result['response'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Заменяем строку на распарсенный массив
                $dataToSave['response'] = $decodedResponse;
            }
        }
        
        // Сохраняем обработанные данные
        $this->report->saveApiResponse($this->target, $dataToSave);
        $this->report->updateApiStatus($this->target, 'completed');
        
        Log::info("✅ TARGET COMPLETED: {$this->target}", [
            'report_id' => $this->report->id,
            'attempts' => $this->attempt
        ]);
        
        $this->checkIfAllCompleted();
    }
    
    protected function handleError(string $error)
    {
        $this->report->updateApiStatus($this->target, 'failed');
        $this->report->addMetaData("errors.{$this->target}", $error);
        
        Log::error("❌ TARGET FAILED: {$this->target}", [
            'report_id' => $this->report->id,
            'error' => $error
        ]);
        
        $this->checkIfAllCompleted();
    }
    
    protected function scheduleNextCheck()
    {
        $maxAttempts = config('contragent.max_polling_attempts', 30);
        
        if ($this->attempt >= $maxAttempts) {
            Log::error("❌ MAX ATTEMPTS REACHED FOR {$this->target} ({$maxAttempts})");
            $this->handleError('Превышено максимальное количество попыток опроса');
            return;
        }
        
        $nextAttempt = $this->attempt + 1;
        $interval = config('contragent.polling_interval', 10);
        
        Log::info("⏰ Scheduling next check for {$this->target} in {$interval}s (attempt {$nextAttempt})");
        
        CheckContragentResultJob::dispatch($this->report, $this->target, $this->idRequest, $nextAttempt)
            ->onQueue('contragent-api')
            ->delay(now()->addSeconds($interval));
        
        $this->report->addMetaData("polling.{$this->target}.attempt_{$this->attempt}", now());
    }
    
    protected function checkIfAllCompleted()
    {
        if ($this->report->allApiRequestsCompleted()) {
            Log::info("🎉 ALL TARGETS COMPLETED FOR REPORT {$this->report->id}, starting aggregation");
            
            $aggregator = app(ContragentAggregator::class);
            $aggregator->aggregate($this->report);
        } else {
            $statuses = $this->report->api_statuses ?? [];
            Log::info("📊 Current statuses: " . json_encode($statuses));
        }
    }
}