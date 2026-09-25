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

        $currentStock = $variant ? $variant->stock_quantity : $this->product->stock_quantity;

        if ($currentStock <= 0) {
            Flux::toast('This item is currently out of stock.', variant: 'danger');
            return;
        }

        if ($this->quantity > $currentStock) {
            Flux::toast("Only {$currentStock} item(s) available in stock.", variant: 'warning');
            $this->quantity = $currentStock;
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

        $this->dispatch('cart-updated');
        Flux::toast('Added to cart!');
        $this->quantity = 1;
    }
};
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white border border-zinc-200/80 p-6 sm:p-10 shadow-xs">
        @php
            $variant = $this->selectedVariant;
            $currentPrice = $variant ? $variant->effective_price : $product->price;
            $currentStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
            $currentImages = ($variant && $variant->images->count() > 0) ? $variant->images : $product->images;
            $initialMainImage = ($variant && $variant->featured_image_url) ? $variant->featured_image_url : $product->featured_image_url;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-14"
            x-data="{ activeImage: '{{ $initialMainImage ?? '' }}' }"
            x-effect="activeImage = '{{ $initialMainImage ?? '' }}'">
            
            {{-- Image Viewer & Multi-Image Gallery --}}
            <div class="space-y-4">
                {{-- Main Featured Image Viewer --}}
                <div class="bg-zinc-100 aspect-[3/4] overflow-hidden flex items-center justify-center relative">
                    <template x-if="activeImage">
                        <img :src="activeImage" alt="{{ $product->name }}" class="w-full h-full object-cover" />
                    </template>
                    <template x-if="!activeImage">
                        <span class="text-zinc-400 text-xs uppercase tracking-widest">No Image Available</span>
                    </template>
                </div>

                {{-- Supporting Image Thumbnails Gallery --}}
                @if ($currentImages->count() > 1)
                    <div>
                        <div class="text-[10px] uppercase font-semibold text-zinc-400 tracking-widest mb-2">More Views:</div>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ($currentImages as $image)
                                @php
                                    $imageUrl = Storage::url($image->image_path);
                                @endphp
                                <button type="button" @click="activeImage = '{{ $imageUrl }}'"
                                    class="aspect-[3/4] overflow-hidden border transition-all p-0.5"
                                    :class="activeImage === '{{ $imageUrl }}' ? 'border-black ring-1 ring-black' : 'border-zinc-200 opacity-70 hover:opacity-100'">
                                    <img src="{{ $imageUrl }}" class="w-full h-full object-cover" />
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            {{-- Product details --}}
            <div class="flex flex-col justify-center space-y-6">
                <div>
                    @if ($product->category)
                        <span class="text-[10px] uppercase tracking-[0.25em] text-zinc-400 block mb-2">
                            {{ $product->category->name }}
                        </span>
                    @endif

                    <h1 class="font-serif text-3xl sm:text-4xl text-zinc-950 font-normal tracking-wide">
                        {{ $product->name }}
                    </h1>
                    
                    {{-- Dynamic Price --}}
                    <div class="font-sans text-xl font-medium text-zinc-900 mt-2">
                        ${{ number_format($currentPrice / 100, 2) }}
                    </div>
                </div>
                
                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed font-light border-y border-zinc-100 py-4">
                    {{ $product->description }}
                </p>

                {{-- Variant Selector --}}
                @if ($product->has_variants)
                    <div>
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
                
                {{-- Stock Status Indicator --}}
                <div>
                    @if ($currentStock <= 0)
                        <span class="inline-block bg-zinc-100 text-zinc-700 text-[10px] font-semibold tracking-widest uppercase px-2.5 py-1">
                            Out of Stock
                        </span>
                    @elseif ($currentStock <= 3)
                        <span class="inline-block bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-semibold tracking-widest uppercase px-2.5 py-1">
                            Low Stock: Only {{ $currentStock }} left!
                        </span>
                    @else
                        <span class="inline-block bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-semibold tracking-widest uppercase px-2.5 py-1">
                            In Stock ({{ $currentStock }} available)
                        </span>
                    @endif
                </div>

                {{-- In-Store Pickup Callout --}}
                <div class="text-[11px] text-zinc-500 bg-[#faf8f5] p-3 border border-zinc-200">
                    🛍️ Available for <strong>In-Store Pickup & Pay at Counter</strong> in Nassau, Bahamas.
                </div>

                <div class="flex items-center gap-4 pt-2">
                    {{-- Quantity input --}}
                    <div class="w-24">
                        <flux:input type="number" wire:model="quantity" min="1" max="{{ $currentStock > 0 ? $currentStock : 1 }}" :disabled="$currentStock <= 0" />
                    </div>
                    
                    {{-- Add to Cart button --}}
                    @if ($currentStock > 0)
                        <button type="button" wire:click="addToCart" class="flex-1 bg-black hover:bg-zinc-800 text-white py-3 px-6 text-xs font-semibold tracking-[0.25em] uppercase transition-colors">
                            Add to Bag
                        </button>
                    @else
                        <button type="button" disabled class="flex-1 bg-zinc-200 text-zinc-400 py-3 px-6 text-xs font-semibold tracking-[0.25em] uppercase cursor-not-allowed">
                            Out of Stock
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>