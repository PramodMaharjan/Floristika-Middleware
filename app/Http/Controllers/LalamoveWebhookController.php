<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class LalamoveWebhookController extends Controller
{

     public function updateOrderStatus(Request $request)
     {
        $payload = $request->getContent();
        Log::info('Webhook Payload:', ['payload' => $payload]);

        $data = json_decode($payload, true);

        $eventType = $data['eventType'] ?? null;
        $orderData = $data['data']['order'] ?? null;

        if (!$eventType || !$orderData || !isset($orderData['orderId'])) {
            return response('Ignored', 200);
        }

        $lalamoveOrderId = $orderData['orderId'];
        $orders = Order::where('lalamove_order_id', $lalamoveOrderId)->get();

        if ($orders->isEmpty()) {
            return response('Order not found', 404);
        }

        switch ($eventType) {

            case 'ORDER_STATUS_CHANGED':
                $shipmentStatus = $orderData['status'] ?? null;
                if ($shipmentStatus) {
                    foreach ($orders as $order) {
                        if (in_array($shipmentStatus, ['ASSIGNING_DRIVER', 'REJECTED', 'CANCELED'])) {
                            $order->lalamove_driver_info = null;
                        }

                        $order->shipment_status = $this->formatStatusTitle($shipmentStatus);
                        $order->save();
                    }
                }
                break;

            case 'DRIVER_ASSIGNED':
                $driver = $data['data']['driver'] ?? null;

                if ($driver) {
                    foreach ($orders as $order) {
                        $order->lalamove_driver_info = json_encode([
                            'driver_id' => $driver['driverId'] ?? null,
                            'name'      => $driver['name'] ?? null,
                            'phone'     => $driver['phone'] ?? null,
                            'plate'     => $driver['plateNumber'] ?? null,
                            'photo'     => $driver['photo'] ?? null,
                        ]);
                        $order->save();
                    }
                }
                break;

            default:
                return response('Ignored', 200);
        }

        return response('OK', 200);
    }
    
    protected function formatStatusTitle(string $status): string
    {
        $statusWithSpaces = str_replace("_", " ", strtolower($status));
        return ucwords($statusWithSpaces);
    }
}