@foreach ($orders as $order)
    <tr>
        <td>{{ $order->order_number }}</td>
        <td>{{ $order->source }}</td>
        <td>{{ $order->delivery_date }}</td>
        <td>{{ $order->delivery_time }}</td>
        <td>{{ $order->products }}</td>
        <td>{{ $order->city }}</td>
        <td>{{ $order->postal_code }}</td>
        <td>{{ $order->delivery_partners }}</td>
        <td>{{ $order->status }}</td>
        <td>{{ $order->pl }}</td>
        <td>{{ $order->mc }}</td>
        <td>{{ $order->do }}</td>
    </tr>
@endforeach

@if ($orders->isEmpty())
    <tr>
        <td colspan="12" class="text-center">No results found.</td>
    </tr>
@endif