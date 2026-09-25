<?php

use Livewire\Component;
use App\Services\CartService;
use App\Models\Product;
use App\Models\ProductVariant;
use Flux\Flux;

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

    public function increment(string $key, CartService $cartService)
    {
        if (! isset($this->cartItems[$key])) {
            return;
        }

        $item = $this->cartItems[$key];
        $currentQty = (int) $item['quantity'];

        // Check maximum stock available
        $availableStock = 0;
        if (! empty($item['variant_id'])) {
            $variant = ProductVariant::find($item['variant_id']);
            $availableStock = $variant ? $variant->stock_quantity : 0;
        } else {
            $product = Product::find($item['product_id']);
            $availableStock = $product ? $product->stock_quantity : 0;
        }

        if ($currentQty >= $availableStock) {
            Flux::toast("Only {$availableStock} available in stock.", variant: 'warning');
            return;
        }

        $cartService->updateQuantity($key, $currentQty + 1);
        $this->loadCart($cartService);
        $this->dispatch('cart-updated');
    }

    public function decrement(string $key, CartService $cartService)
    {
        if (! isset($this->cartItems[$key])) {
            return;
        }

        $currentQty = (int) $this->cartItems[$key]['quantity'];
        $cartService->updateQuantity($key, $currentQty - 1);
        $this->loadCart($cartService);
        $this->dispatch('cart-updated');
    }

    public function removeItem(string $key, CartService $cartService)
    {
        $cartService->remove($key);
        $this->loadCart($cartService);
        $this->dispatch('cart-updated');
        Flux::toast('Item removed from cart');
    }
};
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <flux:heading size="xl" class="mb-6">Your Shopping Cart</flux:heading>

    @if(empty($cartItems))
        <flux:card class="text-center py-12">
            <p class="text-gray-500 mb-4">Your cart is empty.</p>
            <flux:button href="/" variant="primary">Continue Shopping</flux:button>
        </flux:card>
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
                                <flux:badge color="zinc" size="sm" class="mt-1">{{ $item['variant_name'] }}</flux:badge>
                            @endif
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                ${{ number_format($item['price'] / 100, 2) }} each
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between md:justify-end gap-6">
                        {{-- Quantity stepper controls --}}
                        <div class="flex items-center border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-white dark:bg-gray-800">
                            <button type="button" wire:click="decrement('{{ $key }}')" class="px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold transition-colors">
                                −
                            </button>
                            <span class="px-4 py-1.5 font-semibold text-sm">
                                {{ $item['quantity'] }}
                            </span>
                            <button type="button" wire:click="increment('{{ $key }}')" class="px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold transition-colors">
                                +
                            </button>
                        </div>

                        <div class="text-right min-w-[5rem]">
                            <span class="font-semibold text-lg">${{ number_format(($item['price'] * $item['quantity']) / 100, 2) }}</span>
                        </div>

                        <flux:button variant="ghost" size="sm" class="text-red-500 hover:text-red-600" wire:click="removeItem('{{ $key }}')">
                            Remove
                        </flux:button>
                    </div>
                </flux:card>
            @endforeach
            
            <div class="flex justify-between items-center pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                <flux:heading size="lg">Total:</flux:heading>
                <flux:heading size="lg">${{ number_format($total / 100, 2) }}</flux:heading>
            </div>
            
            <div class="flex justify-between items-center mt-6">
                <flux:button variant="ghost" href="/">← Continue Shopping</flux:button>
                <flux:button variant="primary" href="/checkout">Proceed to Checkout</flux:button>
            </div>
        </div>
    @endif
</div>