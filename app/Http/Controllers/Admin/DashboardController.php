<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $storeUrl;
    protected $shopifyAccessToken;
    protected $apiVersion;

    public function __construct()
    {
        $this->storeUrl = config('services.shopify.domain');
        $this->shopifyAccessToken = config('services.shopify.access_token');
        $this->apiVersion = config('services.shopify.api_version');
    }
    
    public function index()
    {
        $totalOrders = Order::count();
        $fulfilledOrders = Order::where('fulfillment_status', 'Fulfilled')->count();
        $unfulfilledOrders = Order::where('fulfillment_status', 'Unfulfilled')->count();
        $totalSales = 0;
        $currentMonthSales = 0;
        $fulfilledSales = 0;
        $unfulfilledSales = 0;
        $orders = collect([]);

        return view('admin.dashboard', compact(
            'orders',
            'totalOrders',
            'fulfilledOrders',
            'unfulfilledOrders',
            'totalSales',
            'currentMonthSales',
            'fulfilledSales',
            'unfulfilledSales'
        ));
    }

    public function show(Order $order)
    {
        $orderRawJson = json_decode($order->raw_json, true);
        $productIds = collect($orderRawJson['line_items'])
                            ->pluck('product_id')
                            ->filter()
                            ->unique()
                            ->implode(',');

        $url = "https://{$this->storeUrl}/admin/api/{$this->apiVersion}/products.json?ids={$productIds}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Shopify-Access-Token: {$this->shopifyAccessToken}"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $productsData = json_decode($response, true);
        $productsById = collect($productsData['products'] ?? [])->keyBy('id');
        foreach ($orderRawJson['line_items'] as &$item) {
            $productId = $item['product_id'];
            $variantId = $item['variant_id'] ?? null;

            if (!isset($productsById[$productId])) {
                continue;
            }

            $product  = $productsById[$productId];
            $imageUrl = null;

            if ($variantId && !empty($product['variants'])) {
                foreach ($product['variants'] as $variant) {
                    if ($variant['id'] == $variantId && !empty($variant['image_id'])) {
                        $variantImageId = $variant['image_id'];

                        if (!empty($product['images'])) {
                            foreach ($product['images'] as $image) {
                                if ($image['id'] == $variantImageId && !empty($image['src'])) {
                                    $imageUrl = $image['src'];
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }

            if (!$imageUrl && !empty($product['image']['src'])) {
                $imageUrl = $product['image']['src'];
            }

            if ($imageUrl) {
                $item['image_url'] = $imageUrl;
            }
        }
        //  dd($orderRawJson);
        return view('admin.orders.show', compact('order', 'orderRawJson'));
    }

    public function createShopifyFulfillment(string $orderId, ?string $trackingCompany = null, ?string $trackingNumber = null, ?string $trackingUrl = null)
    {
        $fulfillmentOrdersResponse = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->shopifyAccessToken,
            'Content-Type' => 'application/json'
        ])->get("https://{$this->storeUrl}/admin/api/{$this->apiVersion}/orders/{$orderId}/fulfillment_orders.json");

        if (!$fulfillmentOrdersResponse->successful()) {
            throw new \Exception('Failed to fetch fulfillment orders: ' . $fulfillmentOrdersResponse->body());
        }

        $fulfillmentOrdersData = $fulfillmentOrdersResponse->json();
        $fulfillmentOrders = $fulfillmentOrdersData['fulfillment_orders'] ?? [];

        if (empty($fulfillmentOrders)) {
            throw new \Exception('No fulfillment orders found for order: ' . $orderId);
        }

        foreach ($fulfillmentOrders as $fulfillmentOrder) {
            $fulfillmentOrderId = $fulfillmentOrder['id'];
            $locationId = $fulfillmentOrder['assigned_location_id'];
            $status = $fulfillmentOrder['status'] ?? 'unknown';
            
            if (in_array($status, ['closed', 'cancelled', 'incomplete'])) {
                Log::info('Fulfillment order has unfulfillable status, skipping', [
                    'fulfillment_order_id' => $fulfillmentOrderId,
                    'status' => $status
                ]);
                continue;
            }

            $lineItemsToFulfill = collect($fulfillmentOrder['line_items'])
                ->filter(function ($lineItem) {
                    return isset($lineItem['fulfillable_quantity']) && $lineItem['fulfillable_quantity'] > 0;
                })
                ->map(function ($lineItem) {
                    return [
                        'id' => $lineItem['id'],
                        'quantity' => $lineItem['fulfillable_quantity']
                    ];
                })
                ->toArray();

            $payload = [
                'fulfillment' => [
                    'location_id' => $locationId,
                    'notify_customer' => false,
                    'line_items_by_fulfillment_order' => [
                        [
                            'fulfillment_order_id' => $fulfillmentOrderId,
                            'fulfillment_order_line_items' => $lineItemsToFulfill,
                        ]
                    ]
                ]
            ];

            $trackingInfo = array_filter([
                'company' => $trackingCompany,
                'number'  => $trackingNumber,
                'url'     => $trackingUrl,
            ]);

            if (!empty($trackingInfo)) {
                $payload['fulfillment']['tracking_info'] = $trackingInfo;
            }

            Log::info('Fulfillment payload being sent to Shopify', [
                'order_id' => $orderId,
                'payload' => $payload,
            ]);
            
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $this->shopifyAccessToken,
                'Content-Type' => 'application/json'
            ])->post("https://{$this->storeUrl}/admin/api/{$this->apiVersion}/fulfillments.json", $payload);

            if (!$response->successful()) {
                $errorBody = $response->body();
                throw new \Exception('Failed to create fulfillment: ' . $errorBody);
            }

            Log::info('Fulfillment created successfully', [
                'order_id' => $orderId,
                'fulfillment_order_id' => $fulfillmentOrderId           
             ]);
        }
    }


    protected function storeShopifyOrder($orderId, $response)
    {
        if (isset($response['order'])) {
            $order = Order::where('order_id', $orderId)->first();
            if ($order) {
                $order->raw_json = json_encode($response['order']);
                $order->save();
            }
        }
    }

    protected function shopifyRequest($orderId, $data)
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->shopifyAccessToken,
            'Content-Type' => 'application/json'
        ])->put("https://{$this->storeUrl}/admin/api/{$this->apiVersion}/orders/{$orderId}.json", [
            'order' => $data
        ]);
        return $response->json();
    }

    public function updateCard(Request $request)
    {
        $orderId = $request->order_id;
        Log::info('Shopify update response');
        $note = $request->note;
        // Shopify
        $response = $this->shopifyRequest($orderId, ['note' => $note]);
        // DB
        $this->storeShopifyOrder($orderId, $response);

        return response()->json(['success' => true, 'data' => $response]);
    }

    public function updateDeliveryPartner(Request $request)
    {
        return $this->updateOrderAttributes(
            $request->order_id,
            ['Delivery Partner' => $request->delivery_partner],
            ['delivery_partner' => $request->delivery_partner]
        );
    }

    public function updateDelivery(Request $request)
    {
        return $this->updateOrderAttributes(
            $request->order_id,
            [
                'date' => $request->delivery_date,
                'timeslot' => $request->time_slot
            ]
        );
    }

    protected function updateOrderAttributes(string $orderId, array $noteAttributes, ?array $dbUpdates = null)
    {
        $order = Order::where('order_id', $orderId)->first();

        if (!$order || !$order->raw_json) {
            return response()->json(['success' => false, 'message' => 'Order not found in DB']);
        }

        if ($dbUpdates) {
            foreach ($dbUpdates as $key => $value) {
                $order->{$key} = $value;
            }
            $order->save();
        }

        $orderData = json_decode($order->raw_json, true);
        $existingAttributes = $orderData['note_attributes'] ?? [];

        $attributes = collect($existingAttributes)
            ->mapWithKeys(fn($attr) => [$attr['name'] => $attr['value']])
            ->merge($noteAttributes)
            ->toArray();

        $noteAttributesArray = collect($attributes)
            ->map(fn($value, $name) => ['name' => $name, 'value' => $value])
            ->values()
            ->toArray();

        $response = $this->shopifyRequest($orderId, ['note_attributes' => $noteAttributesArray]);
        $this->storeShopifyOrder($orderId, $response);

        return response()->json(['success' => true, 'data' => $response]);
    }

    public function updateShipping(Request $request)
    {
        $orderId = $request->order_id;
        $shippingAddress = $request->shipping_address;
        $response = $this->shopifyRequest($orderId, ['shipping_address' => $shippingAddress]);
        $this->storeShopifyOrder($orderId, $response);
        return response()->json(['success' => true, 'data' => $response]);
    }

    public function updateSender(Request $request)
    {
        $orderId = $request->order_id;
        $billingAddress = $request->billing_address;
        $response = $this->shopifyRequest($orderId, ['billing_address' => $billingAddress]);
        $this->storeShopifyOrder($orderId, $response);
        return response()->json(['success' => true, 'data' => $response]);
    }

    public function setOrderField(Request $request)
    {
        $validated = $request->validate([
                'ids' => 'required|array',
                'field' => 'required|string',
                'values' => 'nullable|array',
                'tracking_company' => 'nullable|string',
                'tracking_number' => 'nullable|string',
                'tracking_url' => 'nullable|string',
            ]);

            $allowedFields = ['do_no', 'pl_no', 'mc_no', 'fulfillment_status'];
            $field = $validated['field'];

            if (!in_array($field, $allowedFields)) {
                return response()->json(['success' => false, 'message' => 'Invalid field']);
            }

            $ids = $validated['ids'];
            $values = $validated['values'] ?? [];
            $trackingCompany = $validated['tracking_company'] ?? null;
            $trackingNumber = $validated['tracking_number'] ?? null;
            $trackingUrl = $validated['tracking_url'] ?? null;

            foreach ($ids as $id) {
                $order = Order::where('order_id', $id)->first();
                if ($field === 'fulfillment_status') {
                    $status = $values[$id] ?? null;
                    $order->$field = $values[$id];
                    $order->save();
                    if (strcasecmp($status, 'Fulfilled') === 0 && $order->order_id) {
                        $this->createShopifyFulfillment(
                            $order->order_id,
                            $trackingCompany,
                            $trackingNumber,
                            $trackingUrl
                        );
                    }
                } else {
                    $order->$field = isset($values[$id]) ? $values[$id] : ($order->$field + 1);
                    $order->save();
                }

            }
        return response()->json(['success' => true, 'message' => $field . ' quantity updated successfully']);
    }

    public function productImages(Request $request)
    {
        $productIds = collect($request->input('product_ids', []))
            ->filter()
            ->unique()
            ->values();
        $variants = collect($request->input('variants', []));
        $variantMap = $variants->keyBy('product_id');
        $productImages = [];

        Log::info('Variant Map:', $variantMap->toArray());

        if ($productIds->count() > 0) {
            foreach ($productIds->chunk(50) as $chunk) {
                $ids = $chunk->implode(',');
                $url = "https://{$this->storeUrl}/admin/api/{$this->apiVersion}/products.json?ids={$ids}";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "X-Shopify-Access-Token: {$this->shopifyAccessToken}"
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                $productsData = json_decode($response, true);

                if (!empty($productsData['products'])) {
                    foreach ($productsData['products'] as $product) {
                        $imageUrl = null;
                        $productId = $product['id'];
                        
                        if ($variantMap->has($productId)) {
                            $variantId = $variantMap[$productId]['variant_id'];
                            if (!empty($product['variants'])) {
                                foreach ($product['variants'] as $variant) {
                                    if ($variant['id'] == $variantId && !empty($variant['image_id'])) {
                                        $variantImageId = $variant['image_id'];
                                        
                                        if (!empty($product['images'])) {
                                            foreach ($product['images'] as $image) {
                                                if ($image['id'] == $variantImageId && !empty($image['src'])) {
                                                    $imageUrl = $image['src'];
                                                    break 2; 
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                           
                        if (!$imageUrl && !empty($product['image']['src'])) {
                            $imageUrl = $product['image']['src'];
                        }
                        
                        $productImages[$productId] = $imageUrl;
                    }
                }
            }
        }
        return response()->json([ 'images' => $productImages ], 200);
    }

}
