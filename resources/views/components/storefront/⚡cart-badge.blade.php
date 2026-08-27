<?php

use Livewire\Component;
use App\Services\CartService;
use Livewire\Attributes\On;

new class extends Component
{
    public int $cartCount = 0;

    public function mount(CartService $cartService)
    {
        $this->cartCount = $cartService->getTotalQuantity();
    }

    #[On('cart-updated')]
    public function updateCartCount(CartService $cartService)
    {
        $this->cartCount = $cartService->getTotalQuantity();
    }
};
?>

<div class="relative inline-flex items-center">
    <flux:icon.shopping-bag class="w-5 h-5 mr-1" />
    <span>Cart</span>
    @if($cartCount > 0)
        <span class="absolute -top-2 -right-3 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-indigo-600 rounded-full">
            {{ $cartCount }}
        </span>
    @endif
</div>
