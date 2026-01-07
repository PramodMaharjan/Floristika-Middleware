@extends('layouts.admin')

@section('title', 'Order Detail')

@section('content')

@php
    $currencyCode = $orderRawJson['presentment_currency'] ?? 'MYR';
    $currencySymbol = ($currencyCode === 'MYR') ? 'RM ' : '$';
    $tiktokOrderNumber = collect($orderRawJson['note_attributes'] ?? [])
            ->firstWhere('name', 'TikTok Order Number')['value'] ?? null;
@endphp

<div class="main-right-content">
    <div class="order-detail container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Order Details</h5>
                        <span class="text-muted order-no text-end">
                            @if($tiktokOrderNumber)
                                <span class="badge bg-light text-dark me-1">
                                    TikTok #{{ $tiktokOrderNumber }}
                                </span>                            
                            @endif
                            #{{ $order->order_number }}
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr class="table-light">
                                        <th scope="col" style="width: 10%;"></th>
                                        <th scope="col" style="width: 40%;">Item</th>
                                        <th scope="col" style="width: 15%;">Price</th>
                                        <th scope="col" style="width: 15%;">Qty</th>
                                        <th scope="col" style="width: 20%;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($orderRawJson['line_items']))
                                        @foreach ($orderRawJson['line_items'] as $item)
                                            <tr>
                                                <td class="align-middle">
                                                    @if (!empty($item['image_url']))
                                                        <img src="{{ $item['image_url']}}" alt="{{ $item['name'] }}" class="img-fluid rounded" style="max-width: 80px;">
                                                    @else
                                                        <img src="" alt="No Image Available" class="img-fluid rounded" style="max-width: 80px;"  loading="lazy">
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <div class="fw-bold">{{ $item['name'] }}</div>
                                                    @if (!empty($item['variant_title']))
                                                        <small class="text-muted">{{ $item['variant_title'] }}</small>
                                                    @endif
                                                </td>
                                                <td class="align-middle">{{ $currencySymbol }} {{ number_format($item['price'], 2) }}</td>
                                                <td class="align-middle">{{ $item['quantity'] }}</td>
                                                <td class="align-middle">{{ $currencySymbol }} {{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center">No items found for this order.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row p-4">
                            @php
                                $totalItems = count($orderRawJson['line_items']);
                                $shippingLine = $orderRawJson['shipping_lines'][0] ?? null;
                                $shippingTitle = $shippingLine['title'] ?? 'Shipping';
                                $shippingPrice = $shippingLine['price'] ?? 0;
                            @endphp   
                             <div class="col-md-12">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Subtotal</span>
                                    <span>Items</span>
                                    <span>{{ $totalItems }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 gap-5 text-center">
                                    <span>Shipping</span>
                                    <span>{{ $shippingTitle }}</span>
                                    <span>{{ $currencySymbol }}{{ $shippingPrice, 2 }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold fs-5">
                                    <span>Total</span>
                                    <span></span>
                                    <span>{{ $currencySymbol }}{{ $orderRawJson['current_total_price'] }}</span>
                                </div> 
                            </div>           
                        </div>
                        <div class="row px-4">
                            <div class="col-md-12">
                                <p class="fw-bold mb-1">Notes:</p>
                                <p class="text-muted">
                                    {{ !empty($orderRawJson['note']) ? $orderRawJson['note'] : 'No notes provided.' }}
                                </p>        
                            </div>
                            <div>
                                @php
                                    $driverInfo = $order->lalamove_driver_info;

                                    if (is_string($driverInfo)) {
                                        $driverInfo = json_decode($driverInfo, true) ?? [];
                                    }
                                    $photo = $driverInfo['photo'] ?? null;
                                    $photo = !empty($photo) ? $photo : asset('images/taxi-driver.png');
                                @endphp
                                @if (!empty($driverInfo))
                                    <div class="p-3 bg-light border rounded shadow-sm d-flex align-items-center gap-3 mb-3 flex-wrap">
                                        <div class="position-relative">
                                            <img src="{{ $photo }}"
                                                class="rounded-circle border driver-photo"
                                                alt="Driver Photo">
                                        </div>

                                        <div class="flex-grow-1">
                                            <div class="fw-bold mb-1">{{ $driverInfo['name'] }}</div>

                                            <div class="small text-muted d-flex flex-wrap">
                                                <span class="me-3"><strong>ID:</strong> {{ $driverInfo['driver_id'] }}</span>
                                                <span class="me-3"><strong>Plate:</strong> {{ $driverInfo['plate'] }}</span>
                                            </div>

                                            <div class="small mt-1">
                                                <strong>Phone:</strong> {{ $driverInfo['phone'] }}
                                            </div>
                                        </div>

                                        <a href="tel:{{ $driverInfo['phone'] }}"
                                            class="btn btn-success d-flex align-items-center gap-1 px-3 py-2 rounded-pill fw-semibold shadow-sm"
                                        >
                                            <i class="bi bi-telephone-fill"></i>
                                            Call
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="row g-2 mb-4">
                                <div class="col-6 col-md-auto">
                                    <button class="btn btn-dark w-100" id="printCard" {{ empty($orderRawJson['note']) ? 'disabled' : '' }}>
                                        Print Card
                                    </button>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <button class="btn btn-dark w-100" id="editPrint">
                                        Print Packing Slip
                                    </button>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <button class="btn btn-warning w-100" id="editCardBtn" data-bs-toggle="modal" data-bs-target="#editCardModal">
                                        Edit Card
                                    </button>
                                </div>
                                <div class="col-6 col-md-auto">
                                    @if(isset($order['fulfillment_status']) && $order['fulfillment_status'] === 'Fulfilled')
                                        <button class="btn btn-secondary w-100" id="fulfillOrder" disabled>
                                            Fulfilled
                                        </button>
                                    @elseif(isset($order['fulfillment_status']) && $order['fulfillment_status'] === 'Cancelled')
                                        <button class="btn btn-danger w-100" id="fulfillOrder" disabled>
                                            Cancelled
                                        </button>
                                    @else
                                        <button class="btn btn-success w-100 position-relative overflow-hidden" id="fulfillOrder">
                                            Mark as Fulfilled
                                        </button>
                                    @endif
                                </div>

                                <div class="col-6 col-md-auto download-waybill-col{{ empty($order['lineclear_waybill_no']) ? ' d-none' : '' }}">
                                    <button class="btn btn-primary w-100" 
                                            id="downloadWaybillBtn"
                                            data-waybill="{{ $order['lineclear_waybill_no'] }}">
                                        Download Waybill
                                    </button>
                                </div>

                                <div class="col-6 col-md-auto view-pod-col{{ empty($order['lineclear_waybill_no']) ? ' d-none' : '' }}">
                                    <button class="btn btn-secondary w-100" id="viewPodBtn">
                                        View POD
                                    </button>
                                </div>

                                <div class="col-6 col-md-auto view-lalamove-col{{ empty($order['lalamove_order_id']) ? ' d-none' : '' }}">
                                    <button class="btn btn-primary w-100" 
                                            id="viewLalamoveBtn"
                                            data-order-id="{{ $order['lalamove_order_id'] ?? '' }}">
                                        Track Lalamove Order
                                    </button>
                                </div>

                                <div class="col-6 col-md-auto view-detrack-col{{ ($order['delivery_partner'] ?? '') !== 'Detrack' ? ' d-none' : '' }}">
                                    <button class="btn btn-primary w-100"
                                        id="viewDetrackBtn"
                                    >
                                        Track Detrack Order
                                    </button>
                                </div>

                                <div class="col-6 col-md-auto{{ empty($order['detrack_assigned_to']) ? ' d-none' : '' }}">
                                    <button class="btn btn-primary w-100"
                                        id="viewVehicleInfoBtn"
                                    >
                                        View Driver Info
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
           
            </div>
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Delivery Details</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-2">
                            @php
                                $deliveryDate = null;
                                $timeSlot = null;
                                $source = $order->source ;

                                if (strtolower($source) === 'tiktok') {
                                    $deliveryDate = $order->delivery_date;
                                    $timeSlot = $order->delivery_time;
                                } else if (!empty($orderRawJson['note_attributes'])) {
                                    foreach ($orderRawJson['note_attributes'] as $attribute) {
                                        if ($attribute['name'] === 'date') {
                                            $deliveryDate = $attribute['value'];
                                        }
                                        if ($attribute['name'] === 'timeslot') {
                                            $timeSlot = $attribute['value'];
                                        }
                                    }
                                }
                            @endphp
                            <li><strong>Delivery Date:</strong> <span class="text-muted">{{ $deliveryDate }}</span></li>
                            <li><strong>Time Slot:</strong> <span class="text-muted">{{ $timeSlot }}</span></li>
                            <li><strong>Self Collect:</strong> <span class="text-muted">{{ is_null($orderRawJson['shipping_address']) ? 'Yes' : 'No' }}</span></li>                        
                        </ul>
                        <button class="btn btn-warning btn-sm" id="editDeliveryBtn" data-bs-toggle="modal" data-bs-target="#editDeliveryModal">Edit Delivery Details</button>
                    </div>
                </div>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Delivery Partner</h6>
                    </div>
                    <div class="card-body">
                        <div>
                            @php
                                $savedDeliveryPartner = $order->delivery_partner ?? null;
                                $deliveryPartners = ['Line Clear', 'Lalamove', 'Detrack', 'FedEx'];
                            @endphp
                            <select class="form-select" id="deliveryPartnerSelect" data-order-id="{{ $order->order_id }}">
                                <option value="" disabled {{ !$savedDeliveryPartner ? 'selected' : '' }}>
                                    Select a delivery partner
                                </option>
                                @foreach ($deliveryPartners as $partner)
                                    <option value="{{ $partner }}" {{ $savedDeliveryPartner === $partner ? 'selected' : '' }}>
                                        {{ $partner }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- <button class="btn btn-warning btn-sm" id="editDeliveryPartnerBtn">Save Delivery Partner</button> --}}
                    </div>
                </div>
                <div class="modal fade" id="fedexModal" tabindex="-1" aria-labelledby="fedexModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="fedexModalLabel">Add Tracking Number</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="trackingNumber" class="form-label">Tracking Number</label>
                                    <input type="text" class="form-control" id="trackingNumber"
                                        value="{{ $tiktokOrderNumber }}" />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary btn-sm" id="saveTrackingBtn">Save Tracking</button>
                            </div>
                        </div>
                    </div>
                </div>
                @if (!empty($orderRawJson['shipping_address']))
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Shipping Details</h6>
                        </div>
                        <div class="card-body">
                            <address class="mb-2">
                                <strong>{{ $orderRawJson['shipping_address']['name'] }}</strong><br>
                                {{ $orderRawJson['shipping_address']['address1'] }}<br>
                                @if (!empty($orderRawJson['shipping_address']['address2']))
                                    {{ $orderRawJson['shipping_address']['address2'] }}<br>
                                @endif
                                {{ $orderRawJson['shipping_address']['zip'] }} {{ $orderRawJson['shipping_address']['province'] }}, {{ $orderRawJson['shipping_address']['province_code'] }}<br>
                                {{ $orderRawJson['shipping_address']['country'] }}<br>
                                {{ $orderRawJson['shipping_address']['phone'] }}
                            </address>
                            <button class="btn btn-warning btn-sm" id="editShippingBtn" data-bs-toggle="modal" data-bs-target="#editShippingModal">Edit Shipping Details</button>
                        </div>
                    </div>
                @endif
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Sender Details</h6>
                    </div>
                    <div class="card-body">
                        <address class="mb-2">
                            <strong>{{ $orderRawJson['billing_address']['name'] }}</strong><br>
                            {{ $orderRawJson['billing_address']['address1'] }}<br>
                            @if (!empty($orderRawJson['billing_address']['address2']))
                                {{ $orderRawJson['billing_address']['address2'] }}<br>
                            @endif
                            {{ $orderRawJson['billing_address']['zip'] }} {{ $orderRawJson['billing_address']['province'] }}, {{ $orderRawJson['billing_address']['province_code'] }}<br>
                            {{ $orderRawJson['billing_address']['country'] }}<br>
                            {{ $orderRawJson['billing_address']['phone'] }}
                        </address>
                        {{-- <button class="btn btn-warning btn-sm" id="editSenderBtn" data-bs-toggle="modal" data-bs-target="#editSenderModal">Edit Sender Details</button> --}}
                    </div>
                </div>

                
                <div>
            </div>
        </div>
        {{-- <div class="mt-4">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-dark">Back to Dashboard</a>
        </div> --}}
    </div>
</div>

{{-- Edit Card Modal --}}
<x-modals.delivery :order="$order" />
{{-- Edit Card Modal --}}
<x-modals.card />
{{-- Edit Shipping Modal --}}
<x-modals.shipping />
{{-- Edit Sender Modal --}}
<x-modals.sender />
{{-- POD Modal --}}
<x-modals.pod :order="$order" />
@endsection


@push('scripts')
<script>
    window.routes = {
        cardNoteRoute: '{{ route("admin.orders.updateCard") }}',
        deliveryDetailsRoute: '{{ route("admin.orders.updateDelivery") }}',
        shippingAddressRoute: '{{ route("admin.orders.updateShipping") }}',
        billingAddressRoute: '{{ route("admin.orders.updateSender") }}',
        updateDeliveryPartner: '{{ route("admin.orders.updateDeliveryPartner") }}',
        setOrderField: '{{ route("admin.orders.setOrderField") }}',
        createShipmentLineclear: '{{ route("lineclear.create-shipment") }}',
        createShipmentLalamove: '{{ route("lalamove.place-order") }}',
        getLalamoveQuote: '{{ route("lalamove.get-quote") }}',
        waybillDownloadRoute: '{{ route("lineclear.download-waybill") }}',
        PODRoute: '{{ route("lineclear.download-pod") }}',
        lalamovePodImageRoute: '{{ route("lalamove.download-pod-image") }}',
        driverFallbackImage: '{{ asset("images/taxi-driver.png") }}',
        createShipmentDetrack: '{{ route("detrack.create-jobs") }}',
        detrackPODRoute: '{{ route("detrack.download-detrack-pod") }}',
        getDetrackVehicles: '{{ route("detrack.vehicles") }}',
    };
    window.orderData = {
        orderRawJson: {!! json_encode($orderRawJson) !!},
        order: {!! json_encode($order) !!},
        csrfToken: '{{ csrf_token() }}'
    };
</script>
