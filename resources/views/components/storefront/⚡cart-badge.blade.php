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

<div class="relative inline-flex items-center gap-1.5 text-xs tracking-widest font-medium uppercase text-zinc-900 dark:text-zinc-100 hover:text-amber-700 transition-colors">
    <flux:icon.shopping-bag class="w-4 h-4 stroke-[1.5]" />
    <span class="font-sans">({{ $cartCount }})</span>
</div>
