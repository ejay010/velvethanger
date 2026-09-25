<?php

use Livewire\Component;
use App\Models\Order;
use App\Services\OrderService;
use Flux\Flux;

new class extends Component
{
    // Computed property to fetch all orders with items and user
    public function getOrdersProperty()
    {
        // Eager load items.product and items.productVariant so we display items and variant names without N+1 queries
        return Order::with(['user', 'items.product', 'items.productVariant'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Action to update the status of an order using OrderService to handle stock adjustments
    public function updateStatus(int $orderId, string $newStatus, OrderService $orderService)
    {
        $order = Order::findOrFail($orderId);
        $orderService->updateStatus($order, $newStatus);

        $statusLabel = match($newStatus) {
            'ready_for_pickup' => 'Ready for Pickup',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($newStatus)
        };

        Flux::toast("Order #{$order->id} marked as {$statusLabel}.", variant: 'success');
    }
};
?>

<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Order Manager</flux:heading>
            <flux:subheading>Manage incoming in-store pickup orders and customer collections.</flux:subheading>
        </div>
    </div>

    <flux:card>
        @if($this->orders->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <flux:icon name="shopping-bag" class="w-12 h-12 mx-auto mb-3 text-gray-400" />
                <p class="font-medium">No orders have been placed yet.</p>
            </div>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach($this->orders as $order)
                    <div class="py-6 first:pt-0 last:pb-0 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-lg text-gray-900 dark:text-white">Order #{{ $order->id }}</span>
                                    
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
                                </div>
                                <div class="text-sm text-gray-500">
                                    Placed on {{ $order->created_at->format('M j, Y \a\t g:i A') }} • 
                                    <strong>{{ $order->customer_name ?? ($order->user->name ?? 'Guest') }}</strong> 
                                    @if($order->customer_email)
                                        ({{ $order->customer_email }})
                                    @endif
                                </div>
                                @if($order->customer_address)
                                    <div class="text-xs text-gray-500 italic">
                                        Pickup / Customer Notes: {{ $order->customer_address }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-lg mr-2">{{ $order->formatted_total }}</span>

                                @if($order->status === 'pending')
                                    <flux:button size="sm" variant="primary" wire:click="updateStatus({{ $order->id }}, 'ready_for_pickup')">
                                        Mark Ready for Pickup
                                    </flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="updateStatus({{ $order->id }}, 'cancelled')">
                                        Cancel Order
                                    </flux:button>
                                @elseif($order->status === 'ready_for_pickup')
                                    <flux:button size="sm" variant="primary" wire:click="updateStatus({{ $order->id }}, 'completed')">
                                        Mark Collected & Paid
                                    </flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="updateStatus({{ $order->id }}, 'cancelled')">
                                        Cancel Order
                                    </flux:button>
                                @endif
                            </div>
                        </div>

                        {{-- Order Line Items Breakdown --}}
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 text-sm">
                            <div class="font-semibold text-xs text-gray-500 uppercase tracking-wider mb-2">Items in Order</div>
                            <div class="space-y-1">
                                @foreach($order->items as $item)
                                    <div class="flex justify-between items-center text-sm py-1 border-b border-gray-100 dark:border-gray-800 last:border-0">
                                        <div>
                                            <span class="font-medium text-gray-800 dark:text-gray-200">
                                                {{ $item->product?->name ?? 'Product' }}
                                            </span>
                                            @if($item->variant_name)
                                                <span class="text-xs px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 ml-1">
                                                    {{ $item->variant_name }}
                                                </span>
                                            @endif
                                            <span class="text-gray-500 text-xs ml-2">× {{ $item->quantity }}</span>
                                        </div>
                                        <div class="font-medium text-gray-700 dark:text-gray-300">
                                            {{ $item->formatted_subtotal }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </flux:card>
</div>