<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class LineClearService
{
    protected $baseUrl;
    protected $viewTrackBase;
    protected $podBaseUrl;
    protected $token;
    protected $waybillToken;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->baseUrl = config('services.lineclear.prod_base');
        $this->viewTrackBase = config('services.lineclear.viewtrack_base');
        $this->podBaseUrl = config('services.lineclear.pod_base');
        $this->token = config('services.lineclear.token');
        $this->waybillToken = config('services.lineclear.waybill_token');
        $this->username = config('services.lineclear.username');
        $this->password = config('services.lineclear.password');
    }

    public function initiateShipment(array $payload)
    {
        try {
            $url = $this->baseUrl . '/Accounts/CreateShipment';
            $response = Http::withBasicAuth($this->username, $this->password)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload)
                ->throw();

            $data = $response->json();

            Log::info("LineClear API Success", [
                'url' => $url,
                'status' => $response->status(),
                'response' => $data,
            ]);
            return $data;
        } catch (\Throwable $e) {
            Log::error('LineClear API Error', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getShipmentStatus(string $waybills): ?array
    {
        try {
            $url = $this->viewTrackBase . '/ce/1.0/viewandtrack';
            $waybillsArray = array_map('trim', explode(',', $waybills));

            $payload = [
                "SearchType" => "WayBillNumber",
                "WayBillNumber"=> $waybillsArray,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->ok()) {
                return $response->json();
            }
            Log::error("LineClear API error for {$waybills}: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("LineClear API exception for {$waybills}: " . $e->getMessage());
            return null;
        }
    }

    public function retrieveWaybill(string $waybills): array
    {
        $url = $this->baseUrl . '/DownloadWaybill';
        Log::info('Downloading Waybill from URL:', ['url' => $url]);

        $authString = base64_encode("{$this->username}|{$this->password}|{$this->waybillToken}");
        $payload = [
            'WayBillType' => 'Parent Waybills',
            'WayBills'    => $waybills,
            'PrintOption' => 'LC WB'
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $authString,
                'Accept'        => 'application/pdf',
                'Content-Type'  => 'application/json',
            ])->post($url, $payload);

            if ($response->ok()) {
                return [
                    'success'  => true,
                    'pdf'      => $response->body(),
                ];
            }

            return [
                'success' => false,
                'error'   => 'Failed to download Waybill',
                'status'  => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error("Exception while downloading Waybill: " . $e->getMessage());
            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'status'  => 500
            ];
        }
    }

    public function retrievePOD(array|string $waybills): array
    {
        $waybills = is_array($waybills) ? $waybills : array_map('trim', explode(',', $waybills));
        $allPods = [];

        foreach ($waybills as $waybillNo) {
            $url = $this->podBaseUrl . '/getPodFiles?waybillNo=' . urlencode($waybillNo);
            $authString = base64_encode("{$this->username}|{$this->password}|{$this->waybillToken}");
            try {
                $response = Http::withHeaders([
                    'Authorization' => $authString,
                    'Accept'        => 'application/json',
                ])
                ->timeout(30)   
                ->retry(3, 1000) 
                ->get($url);
                
                if ($response->ok()) {
                    $allPods[$waybillNo] = $response->json();
                    Log::info('POD retrieved successfully', [
                        'waybillNo' => $waybillNo,
                        'response' => $allPods[$waybillNo]
                    ]);
                } else {
                    Log::warning("Failed to retrieve POD for {$waybillNo}", [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    $allPods[$waybillNo] = [];
                }

            } catch (\Exception $e) {
                Log::error("Exception while downloading POD for {$waybillNo}: " . $e->getMessage());
                $allPods[$waybillNo] = [];
            }
        }
        return $allPods;
    }
}