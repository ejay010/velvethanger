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

    protected function loadCart(CartService $cartService)
    {
        $this->cartItems = $cartService->getItems();
        $this->total = $cartService->getTotal();
    }

    public function removeItem(string $key, CartService $cartService)
    {
        $cartService->remove($key);
        $this->loadCart($cartService);
        $this->dispatch('cart-updated');
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
            @foreach($cartItems as $key => $item)
                <flux:card class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                    <div class="flex items-center gap-4">
                        @if (!empty($item['image_url']))
                            <img src="{{ $item['image_url'] }}" alt="{{ $item['product_name'] ?? 'Product' }}"
                                class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-gray-700" />
                        @else
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center text-xs text-gray-400">
                                No Img
                            </div>
                        @endif

                        <div>
                            <flux:heading size="md">{{ $item['product_name'] ?? ('Product #' . $item['product_id']) }}</flux:heading>
                            @if (!empty($item['variant_name']))
                                <flux:badge color="indigo" size="sm" class="mt-1">{{ $item['variant_name'] }}</flux:badge>
                            @endif
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Price: ${{ number_format($item['price'] / 100, 2) }} × {{ $item['quantity'] }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <span class="font-semibold text-lg">${{ number_format(($item['price'] * $item['quantity']) / 100, 2) }}</span>
                        <flux:button variant="danger" size="sm" wire:click="removeItem('{{ $key }}')">Remove</flux:button>
                    </div>
                </flux:card>
            @endforeach
            
            <div class="flex justify-between items-center pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                <flux:heading size="lg">Total:</flux:heading>
                <flux:heading size="lg">${{ number_format($total / 100, 2) }}</flux:heading>
            </div>
            
            <div class="flex justify-end mt-6">
                <flux:button variant="primary" href="/checkout">Proceed to Checkout</flux:button>
            </div>
        </div>
    @endif
</div>