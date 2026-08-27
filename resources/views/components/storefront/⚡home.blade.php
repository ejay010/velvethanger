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

        return $query->get();
    }

    public function setCategory(?string $slug)
    {
        $this->category = $slug;
    }
};
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    
    <div class="flex flex-col md:flex-row gap-8">
        {{-- Category Sidebar --}}
        <div class="w-full md:w-64 shrink-0">
            <flux:heading size="lg" class="mb-4">Categories</flux:heading>
            <div class="flex flex-col space-y-2">
                <button 
                    wire:click="setCategory(null)" 
                    class="text-left px-3 py-2 rounded-lg transition-colors {{ $category === null ? 'bg-zinc-100 dark:bg-zinc-800 font-medium' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                    All Products
                </button>
                @foreach($this->categories as $cat)
                    <button 
                        wire:click="setCategory('{{ $cat->slug }}')" 
                        class="text-left px-3 py-2 rounded-lg transition-colors flex items-center justify-between {{ $category === $cat->slug ? 'bg-zinc-100 dark:bg-zinc-800 font-medium' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                        <span>{{ $cat->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Product Grid --}}
        <div class="flex-1">
            <div class="flex justify-between items-end mb-6">
                <flux:heading size="xl">
                    @if($category)
                        {{ $this->categories->firstWhere('slug', $category)?->name ?? 'Category' }}
                    @else
                        All Products
                    @endif
                </flux:heading>
                <span class="text-sm text-gray-500">{{ $this->products->count() }} items</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Loop through the active products --}}
                @forelse($this->products as $product)
                    <flux:card class="flex flex-col h-full hover:shadow-lg transition-shadow">
                        {{-- Product Featured Image --}}
                        <div class="mb-4 aspect-[4/5] bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden flex items-center justify-center relative group">
                            @if ($product->featured_image_url)
                                <img src="{{ $product->featured_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            @else
                                <span class="text-gray-400 text-sm">No Image Available</span>
                            @endif
                            <a href="/products/{{ $product->id }}" class="absolute inset-0 z-10" aria-label="View {{ $product->name }}"></a>
                        </div>

                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-medium text-lg text-gray-900 dark:text-white leading-tight">
                                    <a href="/products/{{ $product->id }}" class="hover:underline">{{ $product->name }}</a>
                                </h3>
                                @if($product->category)
                                    <p class="text-xs text-gray-500 mt-1">{{ $product->category->name }}</p>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Price is stored in cents --}}
                        <div class="font-medium text-lg mt-auto mb-4">${{ number_format($product->price / 100, 2) }}</div>
                        
                        <div class="mt-auto">
                            <flux:button href="/products/{{ $product->id }}" variant="primary" class="w-full">View Details</flux:button>
                        </div>
                    </flux:card>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-500">
                        <p>No products found in this category.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>