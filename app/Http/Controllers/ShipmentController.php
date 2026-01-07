<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\LalamoveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\LineClearService;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ShipmentController extends Controller
{
    protected $lineClear;
    protected $lalamove;

    public function __construct(LineClearService $lineClear, LalamoveService $lalamove)
    {
        $this->lineClear = $lineClear;
        $this->lalamove = $lalamove;
    }

    // Line Clear
    public function createLineclearShipment(Request $request)
    {         
        $ordersData = $request->orders ?? []; 
        $description = $request->description;

        if (empty($ordersData)) {
            return response()->json(["error" => "No order IDs provided"], 400);
        }

        $orderIds = array_map('strval', array_column($ordersData, 'orderId'));
        Log::info('Order IDs extracted from ordersData:', $orderIds);

        $orders = Order::whereIn("order_id", $orderIds)->get();
        $shipmentPayload = ["Shipment" => []];

        foreach ($orders as $order) {
            $matchedOrder = collect($ordersData)->firstWhere('orderId', $order->order_id);
            $sizeTypeCode = strtolower($matchedOrder['size'] ?? '');
            $dimension = $matchedOrder['dimension'] ?? '';
            $configServiceCode = "";
            $productID = "";

            if ($sizeTypeCode === "premium") {
                $configServiceCode = "ST00000019";
                $productID = "";
            } elseif ($sizeTypeCode === "freshbox") {
                $configServiceCode = "ST00000021";
                switch ($dimension) {
                    case "Small (15x20x60)":
                        $productID = "P0149ST00000021";
                        break;
                    case "Medium (25x25x60)":
                        $productID = "P0150ST00000021";
                        break;
                    case "Large (35x35x60)":
                        $productID = "P0151ST00000021";
                        break;
                }
            }

            $rawData = json_decode($order->raw_json, true);

            $shipmentFrom = $rawData["billing_address"] ?? [];

            $shipmentTo = $rawData["shipping_address"] ?? [];
            Log::info("Shipment To: ", $shipmentTo);

            // $insurancePurchase = [];
            // $waybills = [];

            $noteAttributes = $rawData["note_attributes"] ?? [];
            $tiktokOrderNumber = "";
            foreach ($noteAttributes as $attr) {
                if (($attr["name"] ?? "") === "TikTok Order Number") {
                    $tiktokOrderNumber = $attr["value"] ?? "";
                    break;
                }
            }

            $shopifyOrderNumber = $order->order_number;
            $shipmentRef = "ORDER ID #{$shopifyOrderNumber}";
            if (!empty($tiktokOrderNumber)) {
                $shipmentRef .= " | TikTok: {$tiktokOrderNumber}";
            }

            // $insurancePurchase[] = [
            //     "ProductDescription" => "Order #{$rawData['order_number']}",
            //     "Quantity"  => 1,
            //     "UnitPrice" => $rawData["total_price"]
            // ];

            // foreach ($rawData["line_items"] ?? [] as $item) {
            //     $insurancePurchase[] = [
            //         "ProductDescription" => $item["title"] ?? "",
            //         "Quantity" => $item["quantity"] ?? 1,
            //         "UnitPrice" => (float)($item["price"] ?? 0)
            //     ];

            //     $waybills[] = [
            //         "WayBillNo" => "",
            //         "ProductID" => $productID,
            //         "Weight" => "1",
            //         "VolumeWidth" => "1",
            //         "VolumeHeight" => "1",
            //         "VolumeLength" => "1"
            //     ];
            // }

            $waybills = [
                [
                    "WayBillNo"     => "",
                    "ProductID"     => $productID,
                    "Weight"        => "1",
                    "VolumeWidth"   => "1",
                    "VolumeHeight"  => "1",
                    "VolumeLength"  => "1",
                ]
            ];

            $shipmentPayload["Shipment"][] = [
                "ShipmentServiceType" => "Standard Delivery",
                "SenderName" => $shipmentFrom["name"] ?? "",
                "RecipientName" => $shipmentTo["name"] ?? "",
                "ConfigServiceCode" => $configServiceCode,
                "WBSenderDisplayAddress" => "Sender address",
                "ShipmentAddressFrom" => [
                    "CompanyName" => "Floristika.com.my Sdn. Bhd.",
                    "UnitNumber" => "-",
                    "Address" => "16 Jalan Liku, Off Jalan Riong",
                    "Address2" => "",
                    "PostalCode" => "59100",
                    "City" => "Bangsar",
                    "State" => "Kuala Lumpur",
                    "Email" => "marcom@fareastflora.com",
                    "PhoneNumber" => "+60322811668",
                    "ICNumber" => ""
                ],
                "ShipmentAddressTo" => [
                    "CompanyName" => $shipmentTo["company"] ?? "",
                    "UnitNumber" => "-",
                    "Address" => $shipmentTo["address1"] ?? "",
                    "Address2" => $shipmentTo["address2"] ?? "",
                    "PostalCode" => $shipmentTo["zip"] ?? "",
                    "City" => $shipmentTo["city"] ?? "",
                    "State" => $shipmentTo["province"] ?? "",
                    "Email" => $rawData["email"] ?? "",
                    "PhoneNumber" => $shipmentTo["phone"] ?? "",
                    "ICNumber" => ""
                ],
                "RecipientPhone" => $shipmentTo["phone"] ?? "",
                "ParcelType" => "Package",
                "ShipmentRef" => $shipmentRef,
                "ShipmentDescription" => $description,
                "ShipmentType" => "Pickup",
                "PickupAddressType" => "Default",
                "CODAmount" => "",
                "ShipmentCOD" => [
                    "AppliedParcelValue" => 0,
                    "AppliedSalesTaxImportDuty" => 0,
                    "AppliedShippingFee" => 0,
                    "AppliedCOD" => 0,
                    "AppliedOther" => 0,
                    "AppliedSST" => 0
                ],
                // "InsurancePurchase" => $insurancePurchase,
                "WayBill" => $waybills,
                "DONumber" => ""
            ];
        }
        Log::info("Shipment Payload: ", $shipmentPayload);
        $response = $this->lineClear->initiateShipment($shipmentPayload);

        if (!empty($response['Status']) && !empty($response['ResponseData'])) {
            foreach ($orders as $index => $order) {
                if (!empty($response['ResponseData'][$index]['WayBill'])) {
                    $waybillNos = implode(', ', $response['ResponseData'][$index]['WayBill']);
                    
                    $order->lineclear_waybill_no = $waybillNos;
                    $order->shipment_status = 'Awaiting Shipment Handover';
                    $order->delivery_partner = 'Line Clear';
                    $order->save();

                    Log::info("Waybills saved for order", [
                        'order_id' => $order->order_id,
                        'waybills' => $waybillNos
                    ]);
                } else {
                    Log::error("No WayBill for order", ['order_id' => $order->order_id]);
                }
            }
        } else {
            Log::error("LineClear API did not return WayBill", ['response' => $response]);
        }

        return response()->json($response);
    }

    public function downloadLineClearWaybill(Request $request)
    {
        $waybills = $request->get('waybills');
        $result = $this->lineClear->retrieveWaybill($waybills);

        if ($result['success']) {
            return response($result['pdf'], 200)
                ->header('Content-Type', 'application/pdf');
                // ->header('Content-Disposition', "attachment; filename=\"{$result['filename']}\"");
        }
        return response()->json([
            'error'  => $result['error'],
            'status' => $result['status'],
        ], $result['status']);
    }

    public function downloadPOD(Request $request)
    {
        $waybillNo = $request->get('waybillNo');
        $pods = $this->lineClear->retrievePOD($waybillNo)[$waybillNo] ?? [];

        if (empty($pods)) {
            return response()->json([
                'success' => false,
                'message' => "No POD files found for {$waybillNo}",
                'pods'    => []
            ], 200);
        }

        $zip = new ZipArchive();
        $zipPath = tempnam(sys_get_temp_dir(), 'pod_') . '.zip';

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return response()->json(['error' => 'Failed to create ZIP file'], 500);
        }

        foreach ($pods as $url) {
            $fileContents = Http::get($url)->body();
            $filename = basename($url); 
            $zip->addFromString($filename, $fileContents);
        }
        
        $zip->close();
        return response()->download($zipPath, "{$waybillNo}POD.zip")->deleteFileAfterSend(true);
    }
        
}
