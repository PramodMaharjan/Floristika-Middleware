@foreach ($orders as $order)
    <tr data-url="{{ route('admin.orders.show', $order) }}"
        class="{{ $order->fulfillment_status === 'Fulfilled' ? 'fulfilled' : ($order->fulfillment_status === 'Unfulfilled' ? 'unfulfilled' : '') }}"
    >
        <td><input type="checkbox" class="order-select-checkbox" data-order-id="{{ $order->order_id }}"></td>
        <td>{{ $order->order_number }}</td>
        <td>{{ $order->tiktok_order_number }}</td>
        <td>{{ $order->source }}</td>
        @php
            $rawJson = is_string($order->raw_json) ? json_decode($order->raw_json, true) : $order->raw_json;
        @endphp
        <td><strong>{!! empty($rawJson['shipping_address']) ? 'Self Collect' : $order->delivery_partner !!}</strong></td>
        <td> {{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('d-m-Y') : '' }}</td>        
        <td>{{ $order->delivery_time }}</td>
        <td>{{ $order->products }}</td>
        <td>{{ $order->city }}</td>
        <td>{{ $order->postal_code }}</td>
        <td class="fulfillment_status-column">{{ $order->fulfillment_status }}</td>
        <td class="pl_no-column">{{ $order->pl_no }}</td>
        <td class="mc_no-column">{{ $order->mc_no }}</td>
        <td class="do_no-column">{{ $order->do_no }}</td>
    </tr>
@endforeach

@if ($orders->isEmpty())
    <tr>
        <td colspan="13" class="text-center">No results found.</td>
    </tr>
@endif
