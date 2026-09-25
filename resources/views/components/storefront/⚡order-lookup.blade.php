<?php

use Livewire\Component;
use App\Models\Order;
use Flux\Flux;

new class extends Component
{
    // Search fields for looking up an order
    public ?string $order_id = '';
    public string $email = '';

    // The searched order (if found)
    public ?Order $searchedOrder = null;
    public bool $hasSearched = false;

    protected $rules = [
        'order_id' => 'required|numeric',
        'email' => 'required|email',
    ];

    public function mount()
    {
        // Support direct linking from checkout confirmation (e.g. ?order_id=12&email=customer@example.com)
        $initialOrderId = request()->query('order_id');
        $initialEmail = request()->query('email');

        if ($initialOrderId && $initialEmail) {
            $this->order_id = (string) $initialOrderId;
            $this->email = (string) $initialEmail;
            $this->lookupOrder();
        }
    }

    public function lookupOrder()
    {
        $this->validate();
        $this->hasSearched = true;

        // Query the order by ID and match either the customer_email column or associated User email
        $this->searchedOrder = Order::with(['items.product', 'items.productVariant'])
            ->where('id', $this->order_id)
            ->where(function ($query) {
                $query->where('customer_email', $this->email)
                    ->orWhereHas('user', fn ($u) => $u->where('email', $this->email));
            })
            ->first();

        if (! $this->searchedOrder) {
            Flux::toast('No order found with the provided Order # and email address.', variant: 'danger');
        }
    }
};
?>

<div class="max-w-3xl mx-auto px-4 py-8 space-y-8">
    <div class="text-center space-y-2">
        <flux:heading size="xl">Track Your Order</flux:heading>
        <flux:subheading>Enter your Order Number and Email address to check pickup and fulfillment status.</flux:subheading>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-lg bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Order Lookup Search Form --}}
    <flux:card>
        <form wire:submit.prevent="lookupOrder" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <flux:input label="Order Number" wire:model="order_id" placeholder="e.g. 104" required />
            </div>
            <div>
                <flux:input type="email" label="Email Address" wire:model="email" placeholder="you@example.com" required />
            </div>
            <div>
                <flux:button type="submit" variant="primary" class="w-full">
                    Track Order
                </flux:button>
            </div>
        </form>
    </flux:card>

    {{-- Order Results Display --}}
    @if($searchedOrder)
        <flux:card class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4 gap-2">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-400">Order Reference</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">#{{ $searchedOrder->id }}</div>
                    <div class="text-xs text-gray-500">Placed on {{ $searchedOrder->created_at->format('M j, Y \a\t g:i A') }}</div>
                </div>

                <div>
                    @if($searchedOrder->status === 'pending')
                        <flux:badge color="yellow" size="lg">Order Placed & Preparing</flux:badge>
                    @elseif($searchedOrder->status === 'ready_for_pickup')
                        <flux:badge color="blue" size="lg">Ready for In-Store Pickup</flux:badge>
                    @elseif($searchedOrder->status === 'completed')
                        <flux:badge color="green" size="lg">Collected & Completed</flux:badge>
                    @elseif($searchedOrder->status === 'cancelled')
                        <flux:badge color="red" size="lg">Order Cancelled</flux:badge>
                    @else
                        <flux:badge color="gray" size="lg">{{ ucfirst($searchedOrder->status) }}</flux:badge>
                    @endif
                </div>
            </div>

            {{-- Visual Status Timeline Tracker --}}
            @if($searchedOrder->status !== 'cancelled')
                <div class="py-2">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-4">Fulfillment Progress</div>
                    <div class="grid grid-cols-3 text-center relative gap-2">
                        {{-- Step 1: Received --}}
                        <div class="space-y-1">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center font-bold text-sm bg-indigo-600 text-white">
                                ✓
                            </div>
                            <div class="text-xs font-semibold text-gray-900 dark:text-gray-100">1. Placed</div>
                            <div class="text-[11px] text-gray-500">Order recorded</div>
                        </div>

                        {{-- Step 2: Ready for Pickup --}}
                        <div class="space-y-1">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center font-bold text-sm {{ in_array($searchedOrder->status, ['ready_for_pickup', 'completed']) ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500' }}">
                                {{ in_array($searchedOrder->status, ['ready_for_pickup', 'completed']) ? '✓' : '2' }}
                            </div>
                            <div class="text-xs font-semibold {{ in_array($searchedOrder->status, ['ready_for_pickup', 'completed']) ? 'text-gray-900 dark:text-gray-100' : 'text-gray-400' }}">
                                2. Ready for Pickup
                            </div>
                            <div class="text-[11px] text-gray-500">Available at boutique</div>
                        </div>

                        {{-- Step 3: Completed --}}
                        <div class="space-y-1">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center font-bold text-sm {{ $searchedOrder->status === 'completed' ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500' }}">
                                {{ $searchedOrder->status === 'completed' ? '✓' : '3' }}
                            </div>
                            <div class="text-xs font-semibold {{ $searchedOrder->status === 'completed' ? 'text-gray-900 dark:text-gray-100' : 'text-gray-400' }}">
                                3. Collected
                            </div>
                            <div class="text-[11px] text-gray-500">Paid & picked up</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- In-Store Collection Information Banner --}}
            <div class="p-4 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 text-sm text-indigo-900 dark:text-indigo-200 space-y-1">
                <div class="font-semibold flex items-center gap-2">
                    <span>📍 In-Store Pickup Details</span>
                </div>
                <p class="text-xs text-indigo-800 dark:text-indigo-300">
                    Location: <strong>Velvet Hanger Boutique, Nassau, Bahamas</strong><br />
                    Please provide your <strong>Order #{{ $searchedOrder->id }}</strong> upon arrival. Payment can be completed at the counter.
                </p>
                @if($searchedOrder->customer_address)
                    <p class="text-xs text-indigo-700 dark:text-indigo-400 mt-1 italic">
                        Customer Notes: "{{ $searchedOrder->customer_address }}"
                    </p>
                @endif
            </div>

            {{-- Itemized Order Breakdown --}}
            <div>
                <flux:heading size="md" class="mb-3">Order Items</flux:heading>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($searchedOrder->items as $item)
                        <div class="py-3 flex justify-between items-center gap-4">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $item->product?->name ?? 'Product' }}
                                </div>
                                @if($item->variant_name)
                                    <div class="text-xs text-gray-500">
                                        Option: {{ $item->variant_name }}
                                    </div>
                                @endif
                                <div class="text-xs text-gray-400">
                                    Qty: {{ $item->quantity }} × ${{ number_format($item->unit_price / 100, 2) }}
                                </div>
                            </div>
                            <div class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ $item->formatted_subtotal }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700 mt-2 font-bold text-lg">
                    <span>Total Due at Pickup:</span>
                    <span>{{ $searchedOrder->formatted_total }}</span>
                </div>
            </div>
        </flux:card>
    @elseif($hasSearched)
        <flux:card class="text-center py-8 text-gray-500">
            <p>No order found matching Order #{{ $order_id }} and email {{ $email }}.</p>
            <p class="text-xs text-gray-400 mt-1">Please double check your order confirmation details.</p>
        </flux:card>
    @endif
</div>
