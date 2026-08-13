<?php

use Livewire\Component;
use App\Services\CartService;
use App\Models\Product;

new class extends Component
{
    // The items currently in the cart
    public array $cartItems = [];
    public int $total = 0;

    public function mount(CartService $cartService)
    {
        $this->loadCart($cartService);
    }

    // A helper method to refresh our component's state from the session
    protected function loadCart(CartService $cartService)
    {
        $this->cartItems = $cartService->getItems();
        $this->total = $cartService->getTotal();
    }

    // Action to remove a specific item
    public function removeItem(int $productId, CartService $cartService)
    {
        $cartService->remove($productId);
        $this->loadCart($cartService);
    }
};
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <flux:heading size="xl" class="mb-6">Your Shopping Cart</flux:heading>

    @if(empty($cartItems))
        <p class="text-gray-500 mb-4">Your cart is empty.</p>
        <flux:button href="/">Continue Shopping</flux:button>
    @else
        <div class="space-y-4">
            @foreach($cartItems as $productId => $item)
                <flux:card class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                    <div>
                        {{-- Keeping it simple, we just show the ID for now. 
                             Ideally, we'd query the Product model to show its name here. --}}
                        <flux:heading size="md">Product ID: {{ $item['product_id'] }}</flux:heading>
                        <p class="text-sm text-gray-600">Quantity: {{ $item['quantity'] }}</p>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <span class="font-semibold text-lg">${{ number_format(($item['price'] * $item['quantity']) / 100, 2) }}</span>
                        {{-- This button calls the removeItem PHP method above when clicked --}}
                        <flux:button variant="danger" size="sm" wire:click="removeItem({{ $productId }})">Remove</flux:button>
                    </div>
                </flux:card>
            @endforeach
            
            <div class="flex justify-between items-center pt-6 mt-6 border-t border-gray-200">
                <flux:heading size="lg">Total:</flux:heading>
                <flux:heading size="lg">${{ number_format($total / 100, 2) }}</flux:heading>
            </div>
            
            <div class="flex justify-end mt-6">
                <flux:button variant="primary" href="/checkout">Proceed to Checkout</flux:button>
            </div>
        </div>
    @endif
</div>