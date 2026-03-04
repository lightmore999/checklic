<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\Log;

class ContragentAggregator
{
    public function aggregate(Report $report): array
    {
        $responses = $report->api_responses ?? [];
        $statuses = $report->api_statuses ?? [];
        
        $result = [
            'targets' => [],
            'summary' => [
                'total' => count($statuses),
                'success' => 0,
                'failed' => 0,
            ],
            'inn' => $report->inn
        ];
        
        foreach ($statuses as $target => $status) {
            $targetData = [
                'status' => $status,
                'data' => $responses[$target] ?? null,
            ];
            
            $result['targets'][$target] = $targetData;
            
            if ($status === 'completed') {
                $result['summary']['success']++;
            } elseif ($status === 'failed') {
                $result['summary']['failed']++;
            }
        }
        
        // Определяем общий статус
        if ($result['summary']['success'] === $result['summary']['total']) {
            $report->markAsCompleted();
        } elseif ($result['summary']['success'] > 0) {
            $report->markAsPartial();
        } else {
            $report->markAsFailed();
        }
        
        $report->setProcessedData($result);
        $report->addMetaData('aggregated_at', now());
        
        Log::info("Contragent report aggregated", [
            'report_id' => $report->id,
            'success' => $result['summary']['success'],
            'failed' => $result['summary']['failed']
        ]);
        
        return $result;
    }
}