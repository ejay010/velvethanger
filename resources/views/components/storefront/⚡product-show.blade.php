<?php

use Livewire\Component;
use App\Models\Product;
use App\Services\CartService;
use Flux\Flux;

new class extends Component
{
    // The product being viewed
    public Product $product;
    
    // The quantity the user wants to add to their cart
    public int $quantity = 1;

    public function mount(Product $product)
    {
        // Eager load images, featuredImage, and category relationships
        $product->load(['images', 'featuredImage', 'category']);
        $this->product = $product;
    }

    public function addToCart(CartService $cartService)
    {
        $cartService->add($this->product->id, $this->quantity, $this->product->price);
        Flux::toast('Added to cart!');
        $this->quantity = 1;
    }
};
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <flux:card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8"
            x-data="{ activeImage: '{{ $product->featured_image_url ?? '' }}' }">
            
            {{-- Image Viewer & Gallery --}}
            <div class="space-y-4">
                {{-- Main Featured Image Viewer --}}
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg aspect-square overflow-hidden flex items-center justify-center border border-gray-200 dark:border-gray-700">
                    <template x-if="activeImage">
                        <img :src="activeImage" alt="{{ $product->name }}" class="w-full h-full object-cover" />
                    </template>
                    <template x-if="!activeImage">
                        <span class="text-gray-400">No Image Available</span>
                    </template>
                </div>

                {{-- Supporting Image Thumbnails Gallery --}}
                @if ($product->images->count() > 1)
                    <div>
                        <div class="text-xs font-medium text-gray-500 mb-2">More Views:</div>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ($product->images as $image)
                                @php
                                    $imageUrl = Storage::url($image->image_path);
                                @endphp
                                <button type="button" @click="activeImage = '{{ $imageUrl }}'"
                                    class="aspect-square rounded-lg overflow-hidden border-2 transition-all p-0.5"
                                    :class="activeImage === '{{ $imageUrl }}' ? 'border-indigo-600 ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700 opacity-70 hover:opacity-100'">
                                    <img src="{{ $imageUrl }}" class="w-full h-full object-cover rounded" />
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            {{-- Product details --}}
            <div class="flex flex-col justify-center">
                @if ($product->category)
                    <div class="mb-2">
                        <flux:badge color="zinc">{{ $product->category->name }}</flux:badge>
                    </div>
                @endif

                <flux:heading size="xl">{{ $product->name }}</flux:heading>
                <flux:subheading size="lg" class="mb-4">${{ number_format($product->price / 100, 2) }}</flux:subheading>
                
                <p class="mb-6 text-gray-600 dark:text-gray-300">{{ $product->description }}</p>
                
                <div class="flex items-center gap-4">
                    {{-- Quantity input bound to $quantity --}}
                    <div class="w-24">
                        <flux:input type="number" wire:model="quantity" min="1" max="{{ $product->stock_quantity > 0 ? $product->stock_quantity : 99 }}" />
                    </div>
                    
                    {{-- Button to trigger the addToCart action --}}
                    <flux:button variant="primary" wire:click="addToCart">Add to Cart</flux:button>
                </div>
            </div>
        </div>
    </flux:card>
</div>