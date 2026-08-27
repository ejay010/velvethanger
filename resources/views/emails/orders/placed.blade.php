<x-mail::message>
# Thank you for your order!

Hi {{ $order->customer_name }},

We've received your order and we're getting it ready for you.

**Order ID:** #{{ $order->id }}  
**Date:** {{ $order->created_at->format('M d, Y') }}

<x-mail::table>
| Item       | Qty         | Price  |
| :--------- | :--------- | :----- |
@foreach($order->items as $item)
| {{ $item->product_name }} {{ $item->variant_name ? "({$item->variant_name})" : '' }} | {{ $item->quantity }} | ${{ number_format($item->unit_price / 100, 2) }} |
@endforeach
| **Total**  |            | **${{ number_format($order->total_price / 100, 2) }}** |
</x-mail::table>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
