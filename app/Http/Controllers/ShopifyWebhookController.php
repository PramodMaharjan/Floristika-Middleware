<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PostalCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;


class ShopifyWebhookController extends Controller
{
    protected PostalCodeService $postalCodeService;

    public function __construct(PostalCodeService $postalCodeService)
    {
        $this->postalCodeService = $postalCodeService;
    }

    public function createOrder(Request $request)
    {
        if (!$this->verifyShopifyHMAC($request)) {
            Log::warning('Invalid Shopify webhook signature received.');
            return Response::make('Unauthorized', 401);
        }

        
        $payload = $request->json()->all();
        $shopifyOrderId = $payload['id'];
        $orderNumber        = (string)($payload['order_number'] ?? '');
        $email              = $payload['email'] ?? ($payload['contact_email'] ?? null);
        $financialStatus    = isset($payload['financial_status']) ? ucfirst($payload['financial_status']) : null;
        $fulfillmentStatus  = !empty($payload['cancelled_at']) ? 'Cancelled' : (!empty($payload['fulfillment_status']) ? ucfirst($payload['fulfillment_status']) : 'Unfulfilled');
        $totalPrice         = $payload['total_price'] ?? null;
        $firstName = $payload['customer']['first_name'] ?? '';
        $lastName  = $payload['customer']['last_name'] ?? '';
        $customerName = trim("$firstName $lastName");
        $deliveryDate = null;
        $deliveryTime = null;
        $deliveryPartner = null;
        $products = '';

        if (!empty($payload['line_items'])) {
            $titles = array_map(fn($li) => $li['title'] ?? '', $payload['line_items']);
            $products = implode(', ', $titles);
        }

       if (!empty($payload['note_attributes'])) {
            foreach ($payload['note_attributes'] as $attr) {
                if (strcasecmp($attr['name'], 'date') === 0) {
                    $deliveryDate = $attr['value'];
                }
                if (strcasecmp($attr['name'], 'timeslot') === 0) {
                    $deliveryTime = $attr['value'];
                }
            }
        }

        if (isset($payload['fulfillments']) && count($payload['fulfillments']) && !empty($payload['fulfillments'][0]['tracking_company'])) {
            $deliveryPartner = $payload['fulfillments'][0]['tracking_company'];
        }

        $source = $payload['source_name'] !== null ? ucfirst($payload['source_name']) : null;
        if (strtolower($source) === 'web') {
            $source = 'Shopify';
        }

        if (strcasecmp($source, 'tiktok') === 0) {
            $createdAt = $payload['created_at'] ?? null;

            if ($deliveryDate === null && $createdAt) {
                try {
                    $dateObject = new \DateTime($createdAt, new \DateTimeZone('UTC'));
                    $dateObject->setTimezone(new \DateTimeZone('Asia/Kuala_Lumpur'));
                    $hour = (int)$dateObject->format('H');

                    if ($hour < 14) {
                        $deliveryDate = $dateObject->format('Y-m-d');
                    } else {
                        $deliveryDate = $dateObject->modify('+1 day')->format('Y-m-d');
                    }

                    Log::info("TikTok order: delivery_date set to {$deliveryDate} based on Malaysian time.");
                } catch (\Exception $e) {
                    Log::error("Failed to parse created_at date for TikTok order: {$e->getMessage()}");
                }
            }
            if ($deliveryTime === null) {
                $deliveryTime = "10:00 am - 06:00 pm";
                Log::info("TikTok order: delivery_time set to default {$deliveryTime}.");
            }
        }


        $city       = $payload['shipping_address']['city'] ?? null;
        $postalCode = $payload['shipping_address']['zip'] ?? null;

        $zone = null;
        $subzone = null;

        if (!empty($postalCode) && is_numeric($postalCode)) {
            $postalCodeInt = (int)$postalCode;

            $zone = $this->postalCodeService->getZone($postalCodeInt);
            $subzone = $this->postalCodeService->getSubzone($postalCodeInt);
        }

        $rawJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $data = [
            'order_number'      => $orderNumber,
            'order_id'           => (string)$shopifyOrderId,
            'email'             => $email,
            'customer_name'     => $customerName,
            'products'          => $products,
            'total_price'       => $totalPrice,
            'financial_status'  => $financialStatus,
            'fulfillment_status'=> $fulfillmentStatus,
            'delivery_date'     => $deliveryDate,
            'delivery_time'     => $deliveryTime,
            'delivery_partner'  => $deliveryPartner,
            'source'            => $source,
            'city'              => $city,
            'subzone'           => $subzone,
            'zone'               => $zone,
            'postal_code'       => $postalCode,
            'pl_no'             => 0,
            'mc_no'             => 0,
            'do_no'             => 0,
            'raw_json'          => (string) $rawJson,
        ];
        
        Order::create($data);
        Log::info("New Shopify order ID {$shopifyOrderId} created successfully.");
        
        return Response::make('Webhook received successfully', 200);
    }

    public function updateOrder(Request $request)
    {
        if (!$this->verifyShopifyHMAC($request)) {
            Log::warning('Invalid Shopify webhook signature received.');
            return Response::make('Unauthorized', 401);
        }

        $payload = $request->json()->all();
        $shopifyOrderId = $payload['id'];
        $orderNumber        = (string)($payload['order_number'] ?? '');
        $email              = $payload['email'] ?? ($payload['contact_email'] ?? null);
        $financialStatus    = isset($payload['financial_status']) ? ucfirst($payload['financial_status']) : null;
        $fulfillmentStatus  = !empty($payload['cancelled_at']) ? 'Cancelled' : (!empty($payload['fulfillment_status']) ? ucfirst($payload['fulfillment_status']) : 'Unfulfilled');
        $totalPrice         = $payload['total_price'] ?? null;
        $firstName = $payload['customer']['first_name'] ?? '';
        $lastName  = $payload['customer']['last_name'] ?? '';
        $customerName = trim("$firstName $lastName");
        $deliveryDate = null;
        $deliveryTime = null;
        $deliveryPartner = null;
        $products = '';

        if (!empty($payload['line_items'])) {
            $titles = array_map(fn($li) => $li['title'] ?? '', $payload['line_items']);
            $products = implode(', ', $titles);
        }

       if (!empty($payload['note_attributes'])) {
            foreach ($payload['note_attributes'] as $attr) {
                if (strcasecmp($attr['name'], 'date') === 0) {
                    $deliveryDate = $attr['value'];
                }
                if (strcasecmp($attr['name'], 'timeslot') === 0) {
                    $deliveryTime = $attr['value'];
                }
            }
        }

        // if (isset($payload['fulfillments']) && count($payload['fulfillments']) && !empty($payload['fulfillments'][0]['tracking_company'])) {
        //     $deliveryPartner = $payload['fulfillments'][0]['tracking_company'];
        // }

        $source = $payload['source_name'] !== null ? ucfirst($payload['source_name']) : null;
        if (strtolower($source) === 'web') {
            $source = 'Shopify';
        }

        if (strcasecmp($source, 'tiktok') === 0) {
            $createdAt = $payload['created_at'] ?? null;

            if ($deliveryDate === null && $createdAt) {
                try {
                    $dateObject = new \DateTime($createdAt, new \DateTimeZone('UTC'));
                    $dateObject->setTimezone(new \DateTimeZone('Asia/Kuala_Lumpur'));
                    $hour = (int)$dateObject->format('H');

                    if ($hour < 14) {
                        $deliveryDate = $dateObject->format('Y-m-d');
                    } else {
                        $deliveryDate = $dateObject->modify('+1 day')->format('Y-m-d');
                    }

                    Log::info("TikTok order: delivery_date set to {$deliveryDate} based on Malaysian time.");
                } catch (\Exception $e) {
                    Log::error("Failed to parse created_at date for TikTok order: {$e->getMessage()}");
                }
            }
            if ($deliveryTime === null) {
                $deliveryTime = "10:00 am - 06:00 pm";
                Log::info("TikTok order: delivery_time set to default {$deliveryTime}.");
            }
        }

        $city       = $payload['shipping_address']['city'] ?? null;
        $postalCode = $payload['shipping_address']['zip'] ?? null;
        $rawJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $data = [
            'order_number'      => $orderNumber,
            'email'             => $email,
            'customer_name'     => $customerName,
            'products'          => $products,
            'total_price'       => $totalPrice,
            'financial_status'  => $financialStatus,
            'fulfillment_status'=> $fulfillmentStatus,
            'delivery_date'     => $deliveryDate,
            'delivery_time'     => $deliveryTime,
            // 'delivery_partner'  => $deliveryPartner,
            'source'            => $source,
            'city'              => $city,
            'postal_code'       => $postalCode,
            // 'pl_no'             => 0,
            // 'mc_no'             => 0,
            // 'do_no'             => 0,
            'raw_json'          => (string) $rawJson,
        ];
        
        $order = Order::where('order_id', (string)$shopifyOrderId)->first();
        if ($order) {
            $order->update($data);
            Log::info("Shopify order ID {$shopifyOrderId} updated successfully.");
        } else {
            Log::warning("Shopify order ID {$shopifyOrderId} not found. Cannot update.");
        }
            
        return Response::make('Webhook received successfully', 200);
    }

    protected function verifyShopifyHMAC(Request $request)
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $sharedSecret = config('services.shopify.webhook_secret');

        if (!$hmacHeader || !$sharedSecret) {
            return false;
        }

        $payload = $request->getContent(); 
        $computedHmac = base64_encode(hash_hmac('sha256', $payload, $sharedSecret, true));

        return hash_equals($hmacHeader, $computedHmac);
    }
}