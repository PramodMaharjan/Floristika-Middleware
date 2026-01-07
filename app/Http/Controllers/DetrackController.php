<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\DetrackService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class DetrackController extends Controller
{
    protected DetrackService $detrack;

    public function __construct(DetrackService $detrack)
    {
        $this->detrack = $detrack;
    }

    public function createJobs(Request $request)
    {
        $ordersPayload = $request->orders ?? [];
        $jobs = [];
        $orderModels = [];

        foreach ($ordersPayload as $orderPayload) {
            $orderId = $orderPayload['orderId'];
            $note = $orderPayload['note'] ?? '';
            // $assignee = $orderPayload['assigneeName'];
            $orderModel = Order::where("order_id", $orderId)->first(); 
            $orderModels[$orderModel->order_number] = $orderModel;
            $orderData = json_decode($orderModel->raw_json, true) ?? [];
            $shipping = $orderData['shipping_address'] ?? [];
            $items = [];

            foreach ($orderData['line_items'] ?? [] as $lineItem) {
                $items[] = [
                    'id'       => $lineItem['id'],
                    'sku'      => $lineItem['sku'],
                    'quantity' => $lineItem['quantity'] ?? 1,
                ];
            }

            $jobs[] = [
                'type' => 'Delivery',
                'primary_job_status' => 'dispatched',
                'do_number' => 'DO-' . $orderModel->order_number,
                'attempt' => 1,
                'date' => $orderModel->delivery_date,
                'start_date' => $orderModel->delivery_date,
                'tracking_number' => $orderData['token'],
                'order_number' => $orderModel->order_number,
                'address_lat' => $shipping['latitude'] ?? 0,
                'address_lng' => $shipping['longitude'] ?? 0,
                'address' => $shipping['address1'] ?? '',
                'city' => $shipping['city'],
                'state' => $shipping['province'],
                'country' => $shipping['country'],
                'company_name' => 'Floristika.com.my Sdn. Bhd.',
                'postal_code' => $shipping['zip'] ?? '',
                'deliver_to_collect_from' => $shipping['first_name'] ?? '',
                'last_name' => $shipping['last_name'] ?? null,
                'phone_number' => $shipping['phone'] ?? '',
                'sender_phone_number' => '+60322811668',
                'assign_to' => '',
                'notify_email' => $orderData['email'] ?? '',
                "payment_amount" => $orderData['total_price'] ?? null,
                'geocoded_lat' => $shipping['latitude'] ?? 0,
                'geocoded_lng' => $shipping['longitude'] ?? 0,
                'detrack_number' => 'DET' . $orderModel->id,
                'note' => $note,
                'status' => 'dispatched',
                'tracking_status' => 'Info received',
                'items_count' => count($items),
                'items' => $items,
            ];
        }

        try {
            $result = count($jobs) > 1
                ? $this->detrack->createBulkJobs($jobs)
                : $this->detrack->createJob($jobs[0]);

            Log::info('Detrack Job Creation Result', ['result' => $result]);
            $errorMap = [];

            if (!empty($result['errors']) && is_array($result['errors'])) {
                foreach ($result['errors'] as $errorEntry) {

                    if (isset($errorEntry['do_number'])) {
                        $orderNumber = str_replace('DO-', '', $errorEntry['do_number']);
                        $errorDetails = $errorEntry['errors'] ?? [];
                    } else {
                        $orderNumber = $jobs[0]['order_number'] ?? null;
                        $errorDetails = [$errorEntry];
                    }

                    if ($orderNumber) {
                        $errorMap[$orderNumber] = $this->formatErrorMessages($errorDetails);
                    }
                }
            }

            $successful = $result['data']['data'] ?? $result['data'] ?? [];

            if (isset($successful['order_number'])) {
                $successful = [$successful];
            }

            $statusMap = [];
            foreach ($successful as $jobRecord) {
                if (!empty($jobRecord['order_number'])) {
                    $statusMap[$jobRecord['order_number']] = $jobRecord['status'] ?? '';
                }
            }

            $responseList = [];

            foreach ($orderModels as $orderNumber => $orderModel) {
                if (isset($errorMap[$orderNumber])) {
                    $responseList[] = [
                        'order_number' => $orderNumber,
                        'success'      => false,
                        'error'        => $errorMap[$orderNumber],
                    ];
                    continue;
                }

                if (isset($statusMap[$orderNumber])) {
                    $orderModel->shipment_status = ucwords($statusMap[$orderNumber]);
                    $orderModel->delivery_partner = 'Detrack';
                    $orderModel->save();

                    $responseList[] = [
                        'order_number' => $orderNumber,
                        'success'      => true,
                    ];
                    continue;
                }

                $responseList[] = [
                    'order_number' => $orderNumber,
                    'success'      => false,
                    'error'        => $result['message'] ?? 'Unknown error',
                ];
            }

            return response()->json(['results' => $responseList]);

        } catch (\Exception $e) {
            Log::error("Detrack createJobs exception", ['exception' => $e->getMessage()]);
            return response()->json([
                'results' => array_map(function ($orderModel) {
                    return [
                        'order_number' => $orderModel->order_number,
                        'success'      => false,
                        'error'        => 'Internal server error',
                    ];
                }, $orderModels)
            ], 500);
        }
    }

    public function downloadDetrackPOD(Request $request)
    {
        $doNumber = $request->input('do_number');
        $jobs = $this->detrack->getJobByDONumber($doNumber);
        $podUrls = [];

        foreach ($jobs as $job) {
            for ($i = 1; $i <= 5; $i++) {
                $photoKey = "photo_{$i}_file_url";
                if (!empty($job[$photoKey])) {
                    $podUrls[] = $job[$photoKey];
                }
            }
        }
  
        if (empty($podUrls)) {
            Log::info('POD URLs', ['urls' => $podUrls]);
            return response()->json([
                'success' => false,
                'message' => "No POD files found for {$doNumber}",
                'pods'    => []
            ], 200);
        }

        $zip = new ZipArchive();
        $zipPath = tempnam(sys_get_temp_dir(), 'pod_') . '.zip';

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return response()->json(['error' => 'Failed to create ZIP file'], 500);
        }

        foreach ($podUrls as $url) {
            $fileContents = file_get_contents($url); 
            if ($fileContents !== false) {
                $filename = basename(parse_url($url, PHP_URL_PATH));
                $zip->addFromString($filename, $fileContents);
            }
        }

        $zip->close();

        return response()->download($zipPath, "{$doNumber}_POD.zip")->deleteFileAfterSend(true);
    }

    public function vehicles()
    {
        $vehicles = $this->detrack->getVehicles();
        return response()->json($vehicles);
    }

    private function formatErrorMessages(array $errors): string
    {
        $messages = [];
        foreach ($errors as $error) {
            $field = $error['field'] ?? '';
            $codes = $error['codes'] ?? [];

            $fieldParts = explode('_', $field);

            foreach ($fieldParts as $i => $part) {
                if (strlen($part) <= 2 && ctype_lower($part)) {
                    $fieldParts[$i] = strtoupper($part);
                } else {
                    $fieldParts[$i] = ucwords($part);
                }
            }

            $label = implode(' ', $fieldParts);
            $fieldMessages = implode(', ', $codes);

            $messages[] = $label . ' ' . $fieldMessages;
        }

        return implode('; ', $messages);
    }
}
