<?php

use Livewire\Component;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component
{
    // Form fields for basic customer info
    public string $name = '';
    public string $email = '';
    public string $address = '';

    public int $total = 0;
    public array $cartItems = [];

    // Validation rules to ensure the user fills out the form
    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'address' => 'required|string',
    ];

    public function mount(CartService $cartService)
    {
        $this->cartItems = $cartService->getItems();
        $this->total = $cartService->getTotal();

        // If the user is logged in, pre-fill their info from the Auth facade
        if (Auth::check()) {
            $this->name = Auth::user()->name;
            $this->email = Auth::user()->email;
        }
    }

    public function placeOrder(OrderService $orderService, CartService $cartService)
    {
        // 1. Validate the form inputs against the $rules array
        $this->validate();

        // 2. Prevent checkout if cart is empty
        if (empty($this->cartItems)) {
            Flux::toast('Your cart is empty', variant: 'danger');
            return;
        }

        // 3. Process the checkout and save the order in the database
        // Auth::id() will return null if they are a guest, which our schema allows
        $customerDetails = [
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
        ];
        
        try {
            $order = $orderService->processCheckout(Auth::id(), $this->cartItems, $this->total, $customerDetails);
        } catch (\Exception $e) {
            Flux::toast($e->getMessage(), variant: 'danger');
            $this->addError('stock', $e->getMessage());
            return;
        }

        // 4. Clear the cart since the order is placed
        $cartService->clear();

        // 5. Redirect the user to order lookup page with their reference
        return redirect()->route('order.lookup', [
            'order_id' => $order->id,
            'email' => $this->email,
        ])->with('success', 'Order placed successfully! Please present your Order #' . $order->id . ' when collecting in-store.');
    }
};
?>

<div class="max-w-2xl mx-auto px-4 py-8">
    <flux:heading size="xl" class="mb-6">Checkout</flux:heading>

    @if(empty($cartItems))
        <p class="text-gray-500 mb-4">Your cart is empty.</p>
        <flux:button href="/">Go Shopping</flux:button>
    @else
        {{-- wire:submit.prevent stops the page from reloading and calls our PHP method --}}
        <form wire:submit.prevent="placeOrder" class="space-y-6">
            
            <flux:card>
                <flux:heading size="lg" class="mb-4">Contact Information</flux:heading>
                
                <div class="space-y-4">
                    {{-- wire:model links these inputs directly to our component's PHP properties --}}
                    <flux:input label="Full Name" wire:model="name" required />
                    <flux:input type="email" label="Email Address" wire:model="email" required />
                    <flux:input label="Pickup Address / Notes" wire:model="address" required />
                </div>
            </flux:card>

            <flux:card>
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <flux:heading size="lg">Order Total</flux:heading>
                    <flux:heading size="lg">${{ number_format($total / 100, 2) }}</flux:heading>
                </div>
                
                {{-- In the future, this is where Powertranz credit card inputs would go --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-sm text-gray-600 dark:text-gray-400 mb-6 border border-gray-200 dark:border-gray-700">
                    <flux:heading size="sm" class="mb-1">Payment Method: Pay In-Store</flux:heading>
                    Online payment processing (Powertranz) will be added soon. For now, this will reserve your order for in-store payment and pickup.
                </div>

                <flux:button type="submit" variant="primary" class="w-full">Place Order</flux:button>
            </flux:card>
        </form>
    @endif
</div>