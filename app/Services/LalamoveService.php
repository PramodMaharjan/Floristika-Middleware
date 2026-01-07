<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LalamoveService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $secret;
    protected string $market;

    public function __construct()
    {
        $this->baseUrl = config("services.lalamove.prod_base");
        $this->apiKey  = config("services.lalamove.api_key");
        $this->secret  = config("services.lalamove.secret");
        $this->market = config("services.lalamove.market");
    }


    public function createQuotation(array $payload)
    {
        return $this->sendRequest("POST", "/v3/quotations", $payload, "Quotation");
    }


    public function placeOrder(array $payload)
    {
        return $this->sendRequest("POST", "/v3/orders", $payload, "Order");
    }

    public function getOrderDetails(string $orderId)
    {
        $path = "/v3/orders/{$orderId}";
        return $this->sendRequest("GET", $path, [], "Fetch Order Details");
    }


    protected function sendRequest(string $method, string $path, array $payload, string $action)
    {
        $timestamp = (string)(time() * 1000);
        $signature = $this->generateSignature($method, $path, $timestamp, $payload);
        $url = $this->baseUrl . $path;

        try {
            $options = $method === "GET" ? [] : ["json" => $payload];
            $response = Http::withHeaders([
                "Authorization" => "hmac {$this->apiKey}:{$timestamp}:{$signature}",
                "Market" => $this->market,
                "Content-Type" => "application/json",
            ])->send($method, $url, $options);

            $data = $response->json() ?? [];

            if ($response->successful()) {
                Log::info("Lalamove {$action} Success", ["response" => $data]);
                return $data["data"] ?? null;
            }

            Log::error("Lalamove {$action} Failed", [
                "status" => $response->status(),
                "response" => $data
            ]);

            return $this->formatErrorResponse("Failed to process Lalamove {$action}", $data, $response->status());

        } catch (\Throwable $e) {

            return $this->formatExceptionResponse("Unexpected error during Lalamove {$action}", $e);
        }
    }

    protected function generateSignature(string $method, string $path, string $timestamp, ?array $body = null): string
    {
        $raw = "{$timestamp}\r\n" . strtoupper($method) . "\r\n{$path}\r\n\r\n";

        if ($body) {
            $raw .= json_encode($body);
        }

        return hash_hmac("sha256", $raw, $this->secret);
    }

    protected function formatErrorResponse(string $message, array $data, int $status): array
    {
        return [
            "success" => false,
            "message" => $message,
            "errors"  => $data["errors"] ?? null,
            "status"  => $status,
        ];
    }

    protected function formatExceptionResponse(string $message, \Throwable $e): array
    {
        return [
            "success" => false,
            "message" => $message,
            "exception" => $e->getMessage(),
        ];
    }
}
