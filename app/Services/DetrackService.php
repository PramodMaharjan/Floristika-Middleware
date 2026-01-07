<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DetrackService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.detrack.api_key');
        $this->baseUrl = rtrim(config('services.detrack.base'), '/');
    }

    public function createJob(array $job): ?array
    {
        try {
            $payload = [
                'data' => $job  
            ];

            Log::info('Detrack Payload', $payload);

            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
            ])->post($this->baseUrl . '/dn/jobs', $payload);


            $responseData = $response->json();
            
            if ($response->successful()) {
                return $responseData;
            }
            
            return $responseData;

        } catch (\Exception $e) {
            Log::error('Detrack Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    public function createBulkJobs(array $jobs): ?array
    {
        $payload = [
            'data' => $jobs,
        ];

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
            ])->post($this->baseUrl . '/dn/jobs/bulk', $payload);

            $responseData = $response->json();
            
            if ($response->successful()) {
                return $responseData;
            }
            
            return $responseData;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    public function getJobByDONumber(string $doNumber, ?string $from = null, ?string $to = null): array
    {
        $payload = [
            'data' => [
                'type' => 'Delivery',
                'do_number' => $doNumber,
                // 'dates' => [
                //     'from' => $from ?? now()->subMonth()->format('Y-m-d'),
                //     'to' => $to ?? now()->format('Y-m-d'),
                // ],
                'groups' => [
                    ['name' => 'Detrack']
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-API-KEY' => $this->apiKey,
        ])->post($this->baseUrl . '/dn/jobs/search', $payload);

        if ($response->failed()) {
            return [];
        }

        return $response->json()['data'] ?? [];
    }

    public function getVehicles(): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-KEY' => $this->apiKey,
            ])->post('https://app.detrack.com/api/v1/vehicles/view/all.json', []);

            if ($response->successful()) {
                return $response->json()['vehicles'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Detrack getVehicles Exception: ' . $e->getMessage());
            return [];
        }
    }


}
