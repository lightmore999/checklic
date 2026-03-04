<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ContragentApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateContragentRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $report;
    protected $target;
    
    public $timeout = 30;
    public $tries = 3;
    
    public function __construct(Report $report, string $target)
    {
        $this->report = $report;
        $this->target = $target;
    }
    
    public function handle(ContragentApiService $apiService)
    {
        $inn = $this->report->inn;
        
        if (empty($inn)) {
            $this->report->updateApiStatus($this->target, 'failed');
            $this->report->addMetaData("errors.{$this->target}", 'ИНН не указан');
            return;
        }
        
        Log::info("CreateContragentRequestJob", [
            'report_id' => $this->report->id,
            'target' => $this->target,
            'inn' => $inn
        ]);
        
        // Отправляем запрос на создание
        $idRequest = $apiService->createReport($this->target, $inn);
        
        if ($idRequest) {
            // Сохраняем id_request
            $this->report->addMetaData("requests.{$this->target}", $idRequest);
            
            // Обновляем статус
            $this->report->updateApiStatus($this->target, 'pending');
            
            // Планируем первую проверку результата через 2 секунды
            CheckContragentResultJob::dispatch($this->report, $this->target, $idRequest, 1)
                ->onQueue('contragent-api')
                ->delay(now()->addSeconds(config('contragent.polling_interval', 2)));
        } else {
            // Ошибка создания запроса
            $this->report->updateApiStatus($this->target, 'failed');
            $this->report->addMetaData("errors.{$this->target}", 'Не удалось создать запрос');
        }
    }
}