<?php

use Livewire\Component;
use App\Models\Order;
use Flux\Flux;

new class extends Component
{
    // Computed property to fetch all orders
    public function getOrdersProperty()
    {
        // Eager load the user relation so we don't cause N+1 queries when displaying the user's name
        return Order::with('user')->orderBy('created_at', 'desc')->get();
    }

    // Action to update the status of an order
    public function updateStatus(int $orderId, string $newStatus)
    {
        $order = Order::findOrFail($orderId);
        $order->update(['status' => $newStatus]);
        Flux::toast("Order #{$order->id} marked as {$newStatus}.", variant: 'success');
    }
};
?>

<div class="max-w-6xl mx-auto py-8 px-4">
    <flux:heading size="xl" class="mb-6">Order Manager</flux:heading>

    <flux:card>
        @if($this->orders->isEmpty())
            <p class="text-gray-500">No orders have been placed yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Order ID</th>
                            <th class="py-2">Customer</th>
                            <th class="py-2">Date</th>
                            <th class="py-2">Total</th>
                            <th class="py-2">Status</th>
                            <th class="py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->orders as $order)
                            <tr class="border-b last:border-0">
                                <td class="py-3 font-medium text-gray-700">#{{ $order->id }}</td>
                                <td class="py-3">
                                    {{-- If user is null, it was a guest checkout --}}
                                    {{ $order->user ? $order->user->name : 'Guest' }}
                                </td>
                                <td class="py-3 text-sm text-gray-500">
                                    {{ $order->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="py-3 font-medium">
                                    ${{ number_format($order->total_price / 100, 2) }}
                                </td>
                                <td class="py-3">
                                    @if($order->status === 'pending')
                                        <flux:badge color="yellow">Pending</flux:badge>
                                    @elseif($order->status === 'completed')
                                        <flux:badge color="green">Completed</flux:badge>
                                    @elseif($order->status === 'cancelled')
                                        <flux:badge color="red">Cancelled</flux:badge>
                                    @else
                                        <flux:badge color="gray">{{ ucfirst($order->status) }}</flux:badge>
                                    @endif
                                </td>
                                <td class="py-3 text-right flex justify-end gap-2">
                                    @if($order->status === 'pending')
                                        <flux:button size="sm" variant="primary" wire:click="updateStatus({{ $order->id }}, 'completed')">
                                            Mark Complete
                                        </flux:button>
                                        <flux:button size="sm" variant="danger" wire:click="updateStatus({{ $order->id }}, 'cancelled')">
                                            Cancel
                                        </flux:button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </flux:card>
</div>