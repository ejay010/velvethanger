<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

new class extends Component
{
    #[Url]
    public ?string $category = null;

    #[Url]
    public string $search = '';

    public function getCategoriesProperty(): Collection
    {
        return Category::orderBy('name')->get();
    }

    public function getProductsProperty(): Collection
    {
        $query = Product::with(['category', 'featuredImage', 'images'])
            ->where('is_active', true);

        if ($this->category) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $this->category);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $query->get();
    }

    public function setCategory(?string $slug)
    {
        $this->category = $slug;
    }

    public function clearFilters()
    {
        $this->category = null;
        $this->search = '';
    }
};
?>

<div class="space-y-16 pb-16">
    
    {{-- 1. Editorial Luxury Hero Banner --}}
    <section class="relative bg-[#171412] text-white overflow-hidden">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 min-h-[560px] lg:min-h-[640px] items-center">
            
            {{-- Left: Typography & Story Messaging --}}
            <div class="lg:col-span-6 px-6 sm:px-12 py-16 lg:py-24 z-10 space-y-6">
                <div class="text-[11px] sm:text-xs font-semibold uppercase tracking-[0.3em] text-[#d4af37] flex items-center gap-3">
                    <span class="w-8 h-px bg-[#d4af37]/60"></span>
                    Effortless Elegance. Uniquely You.
                </div>

                <div class="space-y-2">
                    <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl tracking-[0.15em] uppercase font-normal leading-[1.1] text-white">
                        The<br />
                        <span class="font-light tracking-[0.18em]">Velvet</span><br />
                        Lifestyle
                    </h1>
                </div>

                <p class="text-sm sm:text-base text-zinc-300 font-light max-w-md leading-relaxed">
                    Timeless style. Modern edge. Designed for women who dress with confidence.
                </p>

                <div class="pt-4">
                    <a href="#featured-collection" class="inline-block bg-white hover:bg-zinc-100 text-black px-8 py-3.5 text-xs font-semibold tracking-[0.25em] uppercase transition-all duration-300 shadow-sm hover:shadow-md hover:scale-[1.02]">
                        Shop New Arrivals
                    </a>
                </div>
            </div>

            {{-- Right: High Fashion Editorial Model Photography --}}
            <div class="lg:col-span-6 relative h-96 lg:h-full min-h-[420px] lg:min-h-[640px]">
                <img 
                    src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=1200&auto=format&fit=crop" 
                    alt="The Velvet Lifestyle Editorial Model" 
                    class="w-full h-full object-cover object-top filter brightness-95 contrast-105"
                />
                <div class="absolute inset-0 bg-gradient-to-t lg:bg-gradient-to-r from-[#171412] via-transparent to-transparent opacity-90 lg:opacity-80"></div>
            </div>
        </div>
    </section>

    {{-- 2. Value Proposition / Trust Bar --}}
    <section class="bg-[#0f0e0d] text-zinc-300 py-8 border-y border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 lg:gap-8 text-center sm:text-left">
                
                {{-- Item 1 --}}
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center shrink-0 text-[#c5a880]">
                        <flux:icon.truck class="w-5 h-5 stroke-[1.5]" />
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-white">Free Shipping</div>
                        <div class="text-[11px] text-zinc-400 mt-0.5">On orders $200+ Bahamas wide</div>
                    </div>
                </div>

                {{-- Item 2 --}}
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center shrink-0 text-[#c5a880]">
                        <flux:icon.arrow-path class="w-5 h-5 stroke-[1.5]" />
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-white">Easy Returns</div>
                        <div class="text-[11px] text-zinc-400 mt-0.5">14 day return policy for store credit</div>
                    </div>
                </div>

                {{-- Item 3 --}}
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center shrink-0 text-[#c5a880]">
                        <flux:icon.lock-closed class="w-5 h-5 stroke-[1.5]" />
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-white">Secure Checkout</div>
                        <div class="text-[11px] text-zinc-400 mt-0.5">Safe & secure payments</div>
                    </div>
                </div>

                {{-- Item 4 --}}
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center shrink-0 text-[#c5a880]">
                        <flux:icon.heart class="w-5 h-5 stroke-[1.5]" />
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-white">Personal Service</div>
                        <div class="text-[11px] text-zinc-400 mt-0.5">We're here to help 242.322.VELVET</div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- 3. "SHOP OUR FAVORITES" Category Spotlight Grid --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="font-serif text-2xl sm:text-3xl tracking-[0.25em] uppercase text-zinc-950 font-normal">
                Shop Our Favorites
            </h2>
            <div class="w-12 h-px bg-zinc-400 mx-auto mt-3"></div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
            {{-- Category 1: Dresses --}}
            <button type="button" wire:click="setCategory('dresses')" class="group text-center focus:outline-none">
                <div class="aspect-[3/4] bg-zinc-200 overflow-hidden mb-3 shadow-xs">
                    <img 
                        src="https://images.unsplash.com/photo-1595777457583-95e059d581b8?q=80&w=600&auto=format&fit=crop" 
                        alt="Dresses Collection" 
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                    />
                </div>
                <div class="font-serif text-sm tracking-[0.2em] uppercase font-medium text-zinc-900 group-hover:text-amber-800 transition-colors">
                    Dresses
                </div>
                <div class="text-[11px] text-zinc-500 font-medium tracking-wider uppercase mt-0.5">
                    Shop Now
                </div>
            </button>

            {{-- Category 2: Tops --}}
            <button type="button" wire:click="setCategory('tops')" class="group text-center focus:outline-none">
                <div class="aspect-[3/4] bg-zinc-200 overflow-hidden mb-3 shadow-xs">
                    <img 
                        src="https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?q=80&w=600&auto=format&fit=crop" 
                        alt="Tops Collection" 
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                    />
                </div>
                <div class="font-serif text-sm tracking-[0.2em] uppercase font-medium text-zinc-900 group-hover:text-amber-800 transition-colors">
                    Tops
                </div>
                <div class="text-[11px] text-zinc-500 font-medium tracking-wider uppercase mt-0.5">
                    Shop Now
                </div>
            </button>

            {{-- Category 3: Bottoms --}}
            <button type="button" wire:click="setCategory('bottoms')" class="group text-center focus:outline-none">
                <div class="aspect-[3/4] bg-zinc-200 overflow-hidden mb-3 shadow-xs">
                    <img 
                        src="https://images.unsplash.com/photo-1509631179647-0177331693ae?q=80&w=600&auto=format&fit=crop" 
                        alt="Bottoms Collection" 
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                    />
                </div>
                <div class="font-serif text-sm tracking-[0.2em] uppercase font-medium text-zinc-900 group-hover:text-amber-800 transition-colors">
                    Bottoms
                </div>
                <div class="text-[11px] text-zinc-500 font-medium tracking-wider uppercase mt-0.5">
                    Shop Now
                </div>
            </button>

            {{-- Category 4: Accessories --}}
            <button type="button" wire:click="setCategory('accessories')" class="group text-center focus:outline-none">
                <div class="aspect-[3/4] bg-zinc-200 overflow-hidden mb-3 shadow-xs">
                    <img 
                        src="https://images.unsplash.com/photo-1584917865442-de89df76afd3?q=80&w=600&auto=format&fit=crop" 
                        alt="Accessories Collection" 
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                    />
                </div>
                <div class="font-serif text-sm tracking-[0.2em] uppercase font-medium text-zinc-900 group-hover:text-amber-800 transition-colors">
                    Accessories
                </div>
                <div class="text-[11px] text-zinc-500 font-medium tracking-wider uppercase mt-0.5">
                    Shop Now
                </div>
            </button>

            {{-- Category 5: Shoes --}}
            <button type="button" wire:click="setCategory('shoes')" class="group text-center focus:outline-none col-span-2 sm:col-span-1">
                <div class="aspect-[3/4] bg-zinc-200 overflow-hidden mb-3 shadow-xs">
                    <img 
                        src="https://images.unsplash.com/photo-1543163521-1bf539c55dd2?q=80&w=600&auto=format&fit=crop" 
                        alt="Shoes Collection" 
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                    />
                </div>
                <div class="font-serif text-sm tracking-[0.2em] uppercase font-medium text-zinc-900 group-hover:text-amber-800 transition-colors">
                    Shoes
                </div>
                <div class="text-[11px] text-zinc-500 font-medium tracking-wider uppercase mt-0.5">
                    Shop Now
                </div>
            </button>
        </div>
    </section>

    {{-- 4. Featured Product Catalog --}}
    <section id="featured-collection" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between border-b border-zinc-200 pb-4 mb-8 gap-4">
            <div>
                <h3 class="font-serif text-2xl tracking-[0.2em] uppercase font-normal text-zinc-900">
                    @if($category)
                        {{ $this->categories->firstWhere('slug', $category)?->name ?? ucfirst($category) }}
                    @else
                        Curated Collection
                    @endif
                </h3>
                <p class="text-xs text-zinc-500 tracking-wider uppercase mt-1">
                    {{ $this->products->count() }} items available for in-store pickup
                </p>
            </div>

            {{-- Category Filters Pill Bar --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                <button 
                    type="button"
                    wire:click="clearFilters" 
                    class="px-3.5 py-1.5 rounded-full border text-xs tracking-wider uppercase font-medium transition-colors whitespace-nowrap {{ $category === null ? 'bg-black text-white border-black' : 'border-zinc-300 text-zinc-700 hover:border-black' }}">
                    All Items
                </button>
                @foreach($this->categories as $cat)
                    <button 
                        type="button"
                        wire:click="setCategory('{{ $cat->slug }}')" 
                        class="px-3.5 py-1.5 rounded-full border text-xs tracking-wider uppercase font-medium transition-colors whitespace-nowrap {{ $category === $cat->slug ? 'bg-black text-white border-black' : 'border-zinc-300 text-zinc-700 hover:border-black' }}">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Products Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-10">
            @forelse($this->products as $product)
                <div class="group flex flex-col">
                    {{-- Product Image Container --}}
                    <div class="aspect-[3/4] bg-zinc-100 overflow-hidden relative mb-4">
                        @if ($product->featured_image_url)
                            <img 
                                src="{{ $product->featured_image_url }}" 
                                alt="{{ $product->name }}" 
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                            />
                        @else
                            <div class="w-full h-full flex items-center justify-center text-xs text-zinc-400">
                                No Image
                            </div>
                        @endif

                        {{-- Stock Pill --}}
                        @if($product->total_stock <= 0)
                            <span class="absolute top-2.5 left-2.5 bg-black/80 text-white text-[10px] font-bold tracking-widest uppercase px-2 py-1">
                                Out of Stock
                            </span>
                        @endif

                        <a href="/products/{{ $product->id }}" class="absolute inset-0 z-10" aria-label="View {{ $product->name }}"></a>
                    </div>

                    {{-- Product Metadata --}}
                    <div class="space-y-1">
                        @if($product->category)
                            <span class="text-[10px] uppercase tracking-[0.2em] text-zinc-400 block">
                                {{ $product->category->name }}
                            </span>
                        @endif
                        <h4 class="font-serif text-sm text-zinc-900 tracking-wider group-hover:text-amber-800 transition-colors">
                            <a href="/products/{{ $product->id }}">{{ $product->name }}</a>
                        </h4>
                        <div class="font-sans text-xs font-semibold text-zinc-900">
                            ${{ number_format($product->price / 100, 2) }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center text-zinc-500">
                    <p class="font-serif text-lg">No products found in this category.</p>
                    <button wire:click="clearFilters" class="mt-3 text-xs uppercase tracking-widest text-black underline font-medium">
                        View All Collections
                    </button>
                </div>
            @endforelse
        </div>
    </section>

    {{-- 5. "ABOUT THE VELVET LIFESTYLE" Story Section --}}
    <section id="about-story" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center bg-[#f7f4ee] border border-zinc-200/80 p-6 sm:p-12">
            
            {{-- Left: Text & Bio Card --}}
            <div class="lg:col-span-6 space-y-6 lg:pr-6">
                <h3 class="font-serif text-2xl sm:text-3xl tracking-[0.2em] uppercase font-normal text-zinc-950 leading-snug">
                    About The Velvet Lifestyle
                </h3>

                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed font-light">
                    For over 15 years, The Velvet Lifestyle has been Nassau's premier destination for chic, sophisticated style. We curate collections that empower women to look and feel their best—every day and every occasion.
                </p>

                <div class="pt-2">
                    <a href="#footer-contact" class="inline-block bg-black hover:bg-zinc-800 text-white px-7 py-3 text-xs font-semibold tracking-[0.25em] uppercase transition-colors">
                        Our Story
                    </a>
                </div>
            </div>

            {{-- Right: Storefront / Interior Architecture Photography --}}
            <div class="lg:col-span-6 aspect-[4/3] lg:aspect-[16/10] overflow-hidden bg-zinc-200">
                <img 
                    src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=1000&auto=format&fit=crop" 
                    alt="The Velvet Lifestyle Nassau Boutique" 
                    class="w-full h-full object-cover filter brightness-95"
                />
            </div>

        </div>
    </section>

</div>