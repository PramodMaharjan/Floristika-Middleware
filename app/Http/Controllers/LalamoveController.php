<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\LalamoveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LalamoveController extends Controller
{
    protected $lalamove;

    public function __construct(LalamoveService $lalamove)
    {
        $this->lalamove = $lalamove;
    }

    public function getLalamoveQuote(Request $request)
    {
        $serviceType = strtoupper($request->serviceType ?? 'MOTORCYCLE');
        $optimizeRoute = $request->optimizeRoute ?? true;
        $specialRequest = $request->specialRequest ?? [];
        $ordersData = $request->orders ?? [];

        $allStops = [
            [
                "coordinates" => [
                    "lat" => "3.1222",
                    "lng" => "101.6745",
                ],
                "address" => "Floristika.com.my Wholesale Florist Malaysia, 16, Jalan Liku, Bangsar, Kuala Lumpur",
            ]
        ];
        $totalQuantity = 0;
        $addressOrderLookup = [];

        foreach ($ordersData as $data) {
            $orderId = $data['orderId'];
            $order = Order::where('order_id', $orderId)->first();
            if (!$order) continue;

            $rawData = json_decode($order->raw_json, true);
            $lat = (string)($rawData["shipping_address"]["latitude"] ?? "");
            $lng = (string)($rawData["shipping_address"]["longitude"] ?? "");
            $address1 = $rawData['shipping_address']['address1'] ?? '';

            if (isset($rawData['line_items']) && is_array($rawData['line_items'])) {
                foreach ($rawData['line_items'] as $item) {
                    $totalQuantity += $item['quantity'] ?? 0;
                }
            }

            if ($lat && $lng) {
                $allStops[] = [
                    "coordinates" => [
                        "lat" => $lat,
                        "lng" => $lng,
                    ],
                    "address" => $address1,
                ];

                $key = strtolower(trim($address1));
                $addressOrderLookup[$key][] = $orderId;
            }
        }

        $quotationPayload = [
            "data" => [
                "serviceType" => $serviceType,
                "specialRequests"=> $specialRequest,
                "stops" => $allStops,
                "item" => [
                    "quantity" => (string)$totalQuantity,
                    "weight" => "LESS_THAN_3KG",
                    "categories" => ["FLOWERS"],
                    "handlingInstructions" => ["KEEP_UPRIGHT", "FRAGILE"]
                ],
                "isRouteOptimized" => (bool)$optimizeRoute,
                "language" => "en_MY",
            ]
        ];

        $quotation = $this->lalamove->createQuotation($quotationPayload);
        
        if (!$quotation || (isset($quotation['success']) && $quotation['success'] === false)) {
            return response()->json([
                "success" => false,
                "type" => "quotation_error",
                "message" => $quotation['message'] ?? "Quotation failed",
                "errors" => $quotation['errors'] ?? [],
                "status" => $quotation['status'] ?? 500,
            ], 500);
        }

        $orderedOrderIds = [];
        $quotedStops = $quotation['stops'] ?? [];

        if (!empty($quotedStops)) {
            array_shift($quotedStops);
        }

        Log::info('Address Order Lookup:', $addressOrderLookup);
        foreach ($quotedStops as $quotedStop) {
            $addrKey = strtolower(trim($quotedStop['address'] ?? ''));
            if ($addrKey && !empty($addressOrderLookup[$addrKey])) {
                $orderedOrderIds[] = array_shift($addressOrderLookup[$addrKey]);
            }
        }
        
        return response()->json([
            'success' => true,
            'quotationId' => $quotation['quotationId'] ?? null,
            'serviceType' => $quotation['serviceType'] ?? null,
            'stops' => $quotation['stops'] ?? [],
            'orderedOrderIds' => $orderedOrderIds,
            'price' => $quotation['priceBreakdown'],
            'distance' => $quotation['distance'] ?? null,
        ]);

        
    }

    public function placeLalamoveOrder(Request $request)
    {
        $selectedQuotation = $request->orders;
        $orderIds = (array) ($selectedQuotation['orderedOrderIds'] ?? []);
        $quotationId = $selectedQuotation['quotationId'];
        $stops = $selectedQuotation['stops'] ?? [];
        $responses = [];
        $recipients = [];
        $senderStop = array_shift($stops);

        $sender = [
            "stopId" => $senderStop['stopId'],
            "name" => "Floristika.com.my Sdn. Bhd.",
            "phone" => "+60322811668",
        ];

        foreach ($stops as $index => $stop) {
            $relatedOrderId = $orderIds[$index] ?? null;
            $relatedOrder = $relatedOrderId ? Order::where('order_id', $relatedOrderId)->first() : null;
            $rawData = $relatedOrder ? json_decode($relatedOrder->raw_json, true) : [];
            $rawPhone = $rawData['shipping_address']['phone'] ?? "";

            if (empty(trim($rawPhone))) {
                $formattedPhone = $sender['phone'];
            } else {
                $formattedPhone = $this->formatPhone($rawPhone);
                if (empty($formattedPhone)) {
                    $formattedPhone = $sender['phone'];
                }
            }

            $recipients[] = [
                "stopId" => $stop['stopId'],
                "name" => $rawData['shipping_address']['name'] ?? "Recipient",
                "phone" => $formattedPhone,
                "remarks" => (string) $rawData['order_number'],
            ];
        }

        $orderPayload = [
            "data" => [
                "quotationId" => $quotationId,
                "sender" => $sender,
                "recipients" => $recipients,
                "isPODEnabled" => true,
                "metadata" => [
                    "orderIds" => implode(",", $orderIds),
                    "storeName" => "Floristika.com.my Sdn. Bhd."
                ],
            ]
        ];

        Log::info('Placing Lalamove order for multiple orders with multiple stops:', $orderPayload);

        try {
            $orderResponse = $this->lalamove->placeOrder($orderPayload);
            Log::info('Lalamove Place Order Response:', ['response' => $orderResponse]);

            if (isset($orderResponse['success']) && $orderResponse['success'] === false) {
                foreach ($orderIds as $orderId) {
                    $order = Order::where('order_id', $orderId)->first();
                    $responses[] = [
                        'order_id' => $orderId,
                        'success' => false,
                        'type' => 'order_error',
                        'message' => $orderResponse['message'] ?? 'Failed to place order',
                        'errors' => $orderResponse['errors'] ?? [],
                        'status' => $orderResponse['status'] ?? 500,
                        'order_number' => $order->order_number ?? null,
                    ];
                }
            } else {
                $status = $orderResponse['status'];
                $lalamoveOrderId = $orderResponse['orderId'] ?? null;
                foreach ($orderIds as $orderId) {
                    $order = Order::where('order_id', $orderId)->first();
                    $order->shipment_status = $this->formatStatusTitle($status);
                    $order->lalamove_order_id = $lalamoveOrderId;
                    $order->delivery_partner = 'Lalamove';
                    $order->save();
                    $responses[] = [
                        'order_id' => $orderId,
                        'success' => true,
                        'message' => 'Lalamove order placed successfully'
                    ];
    
                }
            }
        } catch (\Exception $e) {
            foreach ($orderIds as $orderId) {
                $responses[] = [
                    'order_id' => $orderId,
                    'success' => false,
                    'message' => 'Lalamove API error: ' . $e->getMessage(),
                ];
            }
        }

        return response()->json(['results' => $responses]);
    }

    public function viewOrder($orderId)
    {
        $orderDetails = $this->lalamove->getOrderDetails($orderId);

        if (!$orderDetails) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or failed to fetch details.'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $orderDetails
        ]);
    }

    public function downloadPodImage(Request $request)
    {
        $imageUrl = $request->query('url');
        $fileOrderId = $request->query('orderId');

        try {
            $remoteResponse = Http::get($imageUrl);
            $contentType = $remoteResponse->header('Content-Type', 'image/png');
            $extension = 'png';

            if (str_contains($contentType, 'jpeg') || str_contains($contentType, 'jpg')) {
                $extension = 'jpg';
            } elseif (str_contains($contentType, 'gif')) {
                $extension = 'gif';
            }

            $baseName = "{$fileOrderId}POD";
            $fileName = "{$baseName}.{$extension}";

            return response($remoteResponse->body(), 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error while downloading POD image.',
            ], 500);
        }
    }

    protected function formatStatusTitle(string $status): string
    {
        $statusWithSpaces = str_replace("_", " ", strtolower($status));
        return ucwords($statusWithSpaces);
    }

    private function formatPhone(?string $phone): string
    {
        if (!$phone || trim($phone) === '') {
            return "";
        }

        $clean = preg_replace('/[^\d+]/', '', $phone);

        $digitsOnly = preg_replace('/[^\d]/', '', $clean);
        if (empty($digitsOnly)) {
            return "";
        }

        if (strpos($clean, '+60') === 0) {
            return $clean;
        }

        if (strpos($clean, '60') === 0) {
            return '+' . $clean;
        }

        if (strpos($clean, '0') === 0) {
            $clean = substr($clean, 1);
        }

        return '+60' . $clean;
    }


        
}
