<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class DetrackWebhookController extends Controller
{
    public function updateJobStatus(Request $request)
    {
        $jsonPayload = $request->get('json');
        $data = json_decode($jsonPayload, true);
        Log::info('Received Detrack webhook', ['data' => $data]);
        if (!$data) {
            Log::error('Failed to decode Detrack JSON', ['raw' => $jsonPayload]);
            return response('Invalid JSON payload', 400);
        }
        $orders = isset($data[0]) ? $data : [$data];
        foreach ($orders as $orderData) {
            $orderNumber = $orderData['order_number'] ?? null;
            $jobStatus = $orderData['status'] ?? null;
            $assignedTo  = $orderData['assign_to'] ?? null;
            $order = Order::where('order_number', $orderNumber)->first();
            $order->shipment_status = $this->formatStatusTitle($jobStatus);
            $order->detrack_assigned_to  = $assignedTo;
            $order->save();
            Log::info("Order {$order->order_number} shipment_status updated to {$jobStatus}");
        }
        return response('OK', 200);
    }

    protected function formatStatusTitle(string $status): string
    {
        return ucwords(str_replace('_', ' ', strtolower($status)));
    }
}
