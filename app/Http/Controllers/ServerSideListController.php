<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ServerSideListController extends Controller
{
    public function getOrdersList(Request $request)
    {
        try {
            $columns = [
                'order_id', 'order_number', 'tiktok_order_number', 'source',
                'delivery_partner', 'delivery_date', 'delivery_time', 'products',
                'city', 'postal_code', 'fulfillment_status', 'pl_no', 'mc_no', 'do_no'
            ];

            $length = (int) $request->input('length', 50);
            $start = (int) $request->input('start', 0);
            $orderColumnIndex = (int) $request->input('order.0.column', 0);
            $orderColumn = $columns[$orderColumnIndex] ?? 'order_id';
            $orderDir = $request->input('order.0.dir', 'desc');
            $search = $request->input('search.value', '');
            $query = Order::query();
            $totalData = $query->count();

            if ($request->filled(['start_date', 'end_date'])) {
                $query->whereBetween('delivery_date', [
                    $request->start_date,
                    $request->end_date,
                ]);
            }
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('source', 'like', "%{$search}%")
                      ->orWhere('delivery_partner', 'like', "%{$search}%")
                      ->orWhere('products', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('subzone', 'like', "%{$search}%")
                      ->orWhere('zone', 'like', "%{$search}%")
                      ->orWhere('postal_code', 'like', "%{$search}%")
                      ->orWhere('fulfillment_status', 'like', "%{$search}%");
                });
            }

            if ($request->filled('delivery_partner')) {
                $deliveryPartner = $request->input('delivery_partner');
                if ($deliveryPartner === 'Self Collect') {
                    $query->where(function ($q) {
                        $q->whereNull('raw_json')
                          ->orWhereRaw("json_extract(raw_json, '$.shipping_address') IS NULL")
                          ->orWhereRaw("json_extract(raw_json, '$.shipping_address') = ''")
                          ->orWhereRaw("json_extract(raw_json, '$.shipping_address') = 'null'");
                    });
                } else {
                    $query->where('delivery_partner', $deliveryPartner);
                }
            }

            if ($request->filled('zone')) {
                $query->where('zone', $request->input('zone'));
            }

            if ($request->filled('subzone')) {
                $query->where('subzone', $request->input('subzone'));
            }

            if ($request->filled('fulfillment_status')) {
                $query->where('fulfillment_status', $request->input('fulfillment_status'));
            }

            if ($request->filled('pl_no')) {
                $query->where('pl_no', $request->input('pl_no'));
            }

            if ($request->filled('mc_no')) {
                $query->where('mc_no', $request->input('mc_no'));
            }

            if ($request->filled('do_no')) {
                $query->where('do_no', $request->input('do_no'));
            }

            $summaryQuery = clone $query;
            $totalFiltered = $query->count();
            $orders = $query->orderBy($orderColumn, $orderDir)
                ->skip($start)
                ->take($length)
                ->get();

            $data = $orders->map(function ($order) {
                $rawJson = is_string($order->raw_json)
                    ? json_decode($order->raw_json, true)
                    : $order->raw_json;
                $tiktokOrderNumber = collect($rawJson['note_attributes'] ?? [])->firstWhere('name', 'TikTok Order Number')['value'] ?? '';
                return [
                    'DT_RowAttr' => [
                        'data-url' => route('admin.orders.show', $order),
                        'class' => $order->fulfillment_status === 'Fulfilled'
                            ? 'fulfilled'
                            : ($order->fulfillment_status === 'Unfulfilled' ? 'unfulfilled' : ''),
                    ],
                    'order_id' => $order->order_id,
                    'raw_json' => $order->raw_json,
                    '<input type="checkbox" class="order-select-checkbox" data-order-id="' . ($order->order_id) . '" data-order-number="' . ($order->order_number) . '">',
                    ($order->order_number),
                    ($order->tiktok_order_number = $tiktokOrderNumber),
                    ($order->source),
                    empty($rawJson['shipping_address']) ? 'Self Collect' : ($order->delivery_partner),
                    $order->delivery_date ? Carbon::parse($order->delivery_date)->format('d-m-Y') : '',
                    ($order->delivery_time),
                    ($order->products),
                    ($order->city),
                    ($order->subzone),
                    ($order->zone),
                    ($order->postal_code),
                    ($order->fulfillment_status),
                    ($order->shipment_status),
                    ($order->pl_no),
                    ($order->mc_no),
                    ($order->do_no),
                ];
            });
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $currentMonthSales = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('total_price');

            $summary = $summaryQuery
                ->selectRaw("
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN fulfillment_status = 'Fulfilled' THEN 1 ELSE 0 END) as fulfilled_count,
                    SUM(CASE WHEN fulfillment_status = 'Unfulfilled' THEN 1 ELSE 0 END) as unfulfilled_count,
                    COALESCE(SUM(total_price), 0) as total_sales,
                    COALESCE(SUM(CASE WHEN fulfillment_status = 'Fulfilled' THEN total_price ELSE 0 END), 0) as fulfilled_sales,
                    COALESCE(SUM(CASE WHEN fulfillment_status = 'Unfulfilled' THEN total_price ELSE 0 END), 0) as unfulfilled_sales
                ")
                ->first();
            $summary->current_month_sales = $currentMonthSales;
            
            $filters = [];
            if ($request->boolean('load_filters')) {
                $filterBaseQuery = Order::query();
                $deliveryPartners = (clone $filterBaseQuery)
                ->selectRaw("
                    DISTINCT 
                    CASE
                        WHEN raw_json IS NULL
                        OR JSON_UNQUOTE(JSON_EXTRACT(raw_json, '$.shipping_address')) IN ('', 'null')
                        THEN 'Self Collect'
                        ELSE TRIM(COALESCE(delivery_partner, ''))
                    END AS partner
                ")
                ->pluck('partner')
                ->filter(fn($v) => !empty($v))
                ->values();

                $filters = [
                    'delivery_partner'   => $deliveryPartners,
                    'subzone' => (clone $filterBaseQuery)
                        ->select('subzone')
                        ->distinct()
                        ->pluck('subzone')
                        ->filter(fn($v) => $v !== null && $v !== '')
                        ->values(),
                    'zone' => (clone $filterBaseQuery)
                        ->select('zone')
                        ->distinct()
                        ->pluck('zone')
                        ->filter(fn($v) => $v !== null && $v !== '')
                        ->values(),
                    'fulfillment_status' => (clone $filterBaseQuery)->select('fulfillment_status')->distinct()->pluck('fulfillment_status')->filter()->values(),
                    'pl_no' => (clone $filterBaseQuery)
                                ->select('pl_no')
                                ->distinct()
                                ->pluck('pl_no')
                                ->filter(fn($v) => $v !== null && $v !== '')
                                ->values(),
                    'mc_no' => (clone $filterBaseQuery)
                        ->select('mc_no')
                        ->distinct()
                        ->pluck('mc_no')
                        ->filter(fn($v) => $v !== null && $v !== '')
                        ->values(),
                    'do_no' => (clone $filterBaseQuery)
                        ->select('do_no')
                        ->distinct()
                        ->pluck('do_no')
                        ->filter(fn($v) => $v !== null && $v !== '')
                        ->values(),
                ];
            }

            return response()->json([
                'recordsTotal' => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
                'summary' => $summary,
                'filters' => $filters,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch orders list.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
