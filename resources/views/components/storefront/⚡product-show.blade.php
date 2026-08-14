<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Flux\Flux;

new class extends Component
{
    // The product being viewed
    public Product $product;
    
    // Currently selected variant ID if product has variations
    public ?int $selected_variant_id = null;

    // The quantity the user wants to add to their cart
    public int $quantity = 1;

    public function mount(Product $product)
    {
        $product->load(['variants', 'variants.images', 'variants.featuredImage', 'images', 'featuredImage', 'category']);
        $this->product = $product;

        if ($product->has_variants) {
            $firstVariant = $product->activeVariants->first();
            if ($firstVariant) {
                $this->selected_variant_id = $firstVariant->id;
            }
        }
    }

    public function getSelectedVariantProperty(): ?ProductVariant
    {
        if (! $this->selected_variant_id) {
            return null;
        }

        return $this->product->variants->firstWhere('id', $this->selected_variant_id);
    }

    public function addToCart(CartService $cartService)
    {
        $variant = $this->selectedVariant;

        if ($this->product->has_variants && ! $variant) {
            Flux::toast('Please select a product option.', variant: 'warning');
            return;
        }

        $price = $variant ? $variant->effective_price : $this->product->price;
        $variantId = $variant ? $variant->id : null;
        $variantName = $variant ? $variant->name : null;
        $imageUrl = $variant ? $variant->featured_image_url : $this->product->featured_image_url;

        $cartService->add(
            productId: $this->product->id,
            quantity: $this->quantity,
            price: $price,
            variantId: $variantId,
            variantName: $variantName,
            imageUrl: $imageUrl
        );

        Flux::toast('Added to cart!');
        $this->quantity = 1;
    }
};
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <flux:card>
        @php
            $variant = $this->selectedVariant;
            $currentPrice = $variant ? $variant->effective_price : $product->price;
            $currentStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
            $currentImages = ($variant && $variant->images->count() > 0) ? $variant->images : $product->images;
            $initialMainImage = ($variant && $variant->featured_image_url) ? $variant->featured_image_url : $product->featured_image_url;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8"
            x-data="{ activeImage: '{{ $initialMainImage ?? '' }}' }"
            x-effect="activeImage = '{{ $initialMainImage ?? '' }}'">
            
            {{-- Image Viewer & Multi-Image Gallery --}}
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
                @if ($currentImages->count() > 1)
                    <div>
                        <div class="text-xs font-medium text-gray-500 mb-2">More Views:</div>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ($currentImages as $image)
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
                
                {{-- Dynamic Price --}}
                <flux:subheading size="lg" class="mb-4">${{ number_format($currentPrice / 100, 2) }}</flux:subheading>
                
                <p class="mb-6 text-gray-600 dark:text-gray-300">{{ $product->description }}</p>

                {{-- Variant Selector --}}
                @if ($product->has_variants)
                    <div class="mb-6">
                        <flux:select label="Select Option / Size / Color" wire:model.live="selected_variant_id" required>
                            @foreach ($product->activeVariants as $varOption)
                                <flux:select.option value="{{ $varOption->id }}">
                                    {{ $varOption->name }} 
                                    ({{ $varOption->stock_quantity > 0 ? $varOption->stock_quantity . ' in stock' : 'Out of Stock' }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif
                
                <div class="flex items-center gap-4">
                    {{-- Quantity input --}}
                    <div class="w-24">
                        <flux:input type="number" wire:model="quantity" min="1" max="{{ $currentStock > 0 ? $currentStock : 99 }}" />
                    </div>
                    
                    {{-- Add to Cart button --}}
                    @if ($currentStock > 0)
                        <flux:button variant="primary" wire:click="addToCart">Add to Cart</flux:button>
                    @else
                        <flux:button variant="primary" disabled>Out of Stock</flux:button>
                    @endif
                </div>
            </div>
        </div>
    </flux:card>
</div>