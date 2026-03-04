<?php

namespace App\Jobs;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessContragentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $report;
    
    public $timeout = 60;
    public $tries = 3;
    
    public function __construct(Report $report)
    {
        $this->report = $report;
    }
    
    public function handle()
    {
        $targets = config('contragent.targets', []);
        
        if (empty($targets)) {
            Log::warning("No targets configured for Contragent", [
                'report_id' => $this->report->id
            ]);
            $this->report->markAsCompleted();
            return;
        }
        
        // Инициализируем статусы
        $this->report->initializeApiStatuses($targets);
        
        // Добавляем мета-данные
        $this->report->addMetaData('started_at', now());
        $this->report->addMetaData('targets', $targets);
        $this->report->addMetaData('contragent_inn', $this->report->inn);
        
        Log::info("ProcessContragentJob started", [
            'report_id' => $this->report->id,
            'inn' => $this->report->inn,
            'targets_count' => count($targets)
        ]);
        
        // Для каждого таргета создаем отдельный джоб
        foreach ($targets as $target) {
            // Шаг 1: Отправляем запрос на создание
            CreateContragentRequestJob::dispatch($this->report, $target)
                ->onQueue('contragent-api');
        }
    }
    
    public function failed(\Throwable $exception)
    {
        Log::error("ProcessContragentJob failed", [
            'report_id' => $this->report->id,
            'error' => $exception->getMessage()
        ]);
        
        $this->report->markAsFailed();
        $this->report->addMetaData('fatal_error', $exception->getMessage());
    }
}