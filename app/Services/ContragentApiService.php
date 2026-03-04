<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContragentApiService
{
    protected $apiKey;
    protected $reportUrl;
    protected $resultUrl;
    
    public function __construct()
    {
        $this->apiKey = config('contragent.api_key');
        $this->reportUrl = config('contragent.report_url');
        $this->resultUrl = config('contragent.result_url');
    }
    
    public function createReport(string $target, string $inn): ?string
    {
        try {
            $response = Http::timeout(30)
                ->post($this->reportUrl, [
                    'key' => $this->apiKey,
                    'target' => $target,
                    'inn' => $inn,
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['id_request'] ?? null;
            }
            
            Log::error("Contragent API create failed", [
                'target' => $target,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Contragent API create exception", [
                'target' => $target,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
    
    public function getResult(string $target, string $idRequest): ?array
    {
        try {
            $response = Http::timeout(30)
                ->post($this->resultUrl, [
                    'key' => $this->apiKey,
                    'target' => $target,
                    'id_request' => $idRequest,
                ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error("Contragent API result failed", [
                'target' => $target,
                'id_request' => $idRequest,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Contragent API result exception", [
                'target' => $target,
                'id_request' => $idRequest,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
}