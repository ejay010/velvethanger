<?php

use Livewire\Component;
use App\Models\Product;
use App\Services\CartService;
use Flux\Flux;

new class extends Component
{
    // The product being viewed
    public Product $product;
    
    // The quantity the user wants to add to their cart, bound to the input via wire:model
    public int $quantity = 1;

    /**
     * The mount method acts like a constructor for the component.
     * It runs once when the component is first loaded.
     */
    public function mount(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Action called when the "Add to Cart" button is clicked.
     * We inject our CartService directly into the method.
     */
    public function addToCart(CartService $cartService)
    {
        // 1. Add item to cart
        $cartService->add($this->product->id, $this->quantity, $this->product->price);
        
        // 2. Show a success toast message to the user
        Flux::toast('Added to cart!');
        
        // 3. Reset the quantity input back to 1
        $this->quantity = 1;
    }
};
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <flux:card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Image placeholder --}}
            <div class="bg-gray-100 dark:bg-gray-800 rounded-lg aspect-square flex items-center justify-center">
                <span class="text-gray-400">Image Placeholder</span>
            </div>
            
            {{-- Product details --}}
            <div class="flex flex-col justify-center">
                <flux:heading size="xl">{{ $product->name }}</flux:heading>
                <flux:subheading size="lg" class="mb-4">${{ number_format($product->price / 100, 2) }}</flux:subheading>
                
                <p class="mb-6">{{ $product->description }}</p>
                
                <div class="flex items-center gap-4">
                    {{-- Quantity input bound to $quantity --}}
                    <div class="w-24">
                        <flux:input type="number" wire:model="quantity" min="1" max="{{ $product->stock_quantity }}" />
                    </div>
                    
                    {{-- Button to trigger the addToCart action --}}
                    <flux:button variant="primary" wire:click="addToCart">Add to Cart</flux:button>
                </div>
            </div>
        </div>
    </flux:card>
</div>