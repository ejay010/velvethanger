<?php

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    // Computed property to fetch authenticated customer's orders
    public function getOrdersProperty()
    {
        return Order::with(['items.product', 'items.productVariant'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }
};
?>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">
    <div>
        <flux:heading size="xl">My Orders</flux:heading>
        <flux:subheading>View your past purchases and check the pickup status of your current orders.</flux:subheading>
    </div>

    @if($this->orders->isEmpty())
        <flux:card class="text-center py-12">
            <p class="text-gray-500 mb-4">You haven't placed any orders yet.</p>
            <flux:button href="/" variant="primary">Start Shopping</flux:button>
        </flux:card>
    @else
        <div class="space-y-6">
            @foreach($this->orders as $order)
                <flux:card class="space-y-4">
                    {{-- Order Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 gap-2">
                        <div>
                            <span class="font-bold text-lg text-gray-900 dark:text-white">Order #{{ $order->id }}</span>
                            <div class="text-xs text-gray-500">Placed on {{ $order->created_at->format('M j, Y \a\t g:i A') }}</div>
                        </div>

                        <div class="flex items-center gap-3">
                            @if($order->status === 'pending')
                                <flux:badge color="yellow" size="sm">Pending Preparation</flux:badge>
                            @elseif($order->status === 'ready_for_pickup')
                                <flux:badge color="blue" size="sm">Ready for Pickup</flux:badge>
                            @elseif($order->status === 'completed')
                                <flux:badge color="green" size="sm">Completed</flux:badge>
                            @elseif($order->status === 'cancelled')
                                <flux:badge color="red" size="sm">Cancelled</flux:badge>
                            @else
                                <flux:badge color="gray" size="sm">{{ ucfirst($order->status) }}</flux:badge>
                            @endif

                            <flux:button size="xs" variant="ghost" href="{{ route('order.lookup', ['order_id' => $order->id, 'email' => auth()->user()->email]) }}">
                                Track →
                            </flux:button>
                        </div>
                    </div>

                    {{-- Order Items --}}
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($order->items as $item)
                            <div class="py-2 flex justify-between items-center text-sm">
                                <div>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">
                                        {{ $item->product?->name ?? 'Product' }}
                                    </span>
                                    @if($item->variant_name)
                                        <span class="text-xs px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 ml-1">
                                            {{ $item->variant_name }}
                                        </span>
                                    @endif
                                    <span class="text-xs text-gray-500 ml-1">× {{ $item->quantity }}</span>
                                </div>
                                <div class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ $item->formatted_subtotal }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Order Summary Footer --}}
                    <div class="flex justify-between items-center pt-2 border-t border-gray-100 dark:border-gray-800 text-sm">
                        <span class="text-gray-500">Pay In-Store Total:</span>
                        <span class="font-bold text-base text-gray-900 dark:text-white">{{ $order->formatted_total }}</span>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
