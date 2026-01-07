<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\LineClearService;
use Illuminate\Support\Facades\Log;

class PollLineClearShipments extends Command
{
    protected $signature = 'lineclear:poll';
    protected $description = 'Poll LineClear API for shipment status';
    protected LineClearService $lineClear;

    public function __construct(LineClearService $lineClear)
    {
        parent::__construct();
        $this->lineClear = $lineClear;
    }

    public function handle(): int
    {
        $orders = Order::whereNotNull('lineclear_waybill_no')
            ->where(function ($query) {
                $query->whereNull('shipment_status')
                      ->orWhere('shipment_status', '!=', 'Delivered');
            })
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No pending shipments to poll.');
            return 0;
        }

        foreach ($orders as $order) {
            $waybills = $order->lineclear_waybill_no;

            $response = $this->lineClear->getShipmentStatus($waybills);

            Log::info("Polled shipment status for Order #{$order->order_id}", [
                'waybills' => $waybills,
                'response' => $response,
            ]);

            if (isset($response['message'])) {
                $msg = strtolower($response['message']);

                Log::info("LineClear API Message for Order #{$order->order_id}: {$msg}");

                if (str_contains($msg, 'invalid') || str_contains($msg, 'does not exist in the system')) {
                    $order->shipment_status = 'Cancelled';
                    $order->save();
                    $this->warn("Order #{$order->order_id} marked as Cancelled.");
                    continue;
                }
            }
            

            if (is_array($response) && isset($response[0][0])) {
                $data = $response[0][0];
                $status = $data['Status'];
                $order->shipment_status = $status;
                $order->save();
                $this->info("Updated Order #{$order->order_id}: {$status}");
            } else {
                Log::warning("Invalid or empty tracking data for {$waybills}");
                $this->warn("No valid data for Order #{$order->order_id}");
            }
        }

        return 0;
    }
}
