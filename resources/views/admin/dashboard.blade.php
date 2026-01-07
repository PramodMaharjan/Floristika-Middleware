@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <x-admin-panel 
        :orders="$orders" 
        :total-orders="$totalOrders" 
        :fulfilledOrders="$fulfilledOrders" 
        :unfulfilledOrders="$unfulfilledOrders"
        :totalSales="$totalSales"
        :currentMonthSales="$currentMonthSales"
        :fulfilledSales="$fulfilledSales"
        :unfulfilledSales="$unfulfilledSales"
    />
@endsection

@push('scripts')
    <script>
       window.routes = {
            setOrderField: '{{ route("admin.orders.setOrderField") }}',
            createShipmentLineclear: '{{ route("lineclear.create-shipment") }}', 
            createShipmentLalamove: '{{ route("lalamove.place-order") }}',
            getLalamoveQuote: '{{ route("lalamove.get-quote") }}',
            createShipmentDetrack: '{{ route("detrack.create-jobs") }}',
            productImages: '{{ route("admin.orders.productImages") }}',
            getDetrackVehicles: '{{ route("detrack.vehicles") }}',
        };
        window.orderData = {
            csrfToken: '{{ csrf_token() }}',
        };
    </script>
@endpush
