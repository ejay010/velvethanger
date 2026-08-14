<?php

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Collection;

new class extends Component
{
    /**
     * Computed property to fetch active products with featured images.
     */
    public function getProductsProperty(): Collection
    {
        return Product::with(['category', 'featuredImage', 'images'])
            ->where('is_active', true)
            ->get();
    }
};
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <flux:heading size="xl" class="mb-6">Welcome to Velvet Hanger Online Boutique</flux:heading>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Loop through the active products --}}
        @forelse($this->products as $product)
            <flux:card class="flex flex-col h-full">
                {{-- Product Featured Image --}}
                <div class="mb-4 aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden flex items-center justify-center">
                    @if ($product->featured_image_url)
                        <img src="{{ $product->featured_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover" />
                    @else
                        <span class="text-gray-400 text-sm">No Image Available</span>
                    @endif
                </div>

                <flux:heading size="lg">{{ $product->name }}</flux:heading>
                
                {{-- Price is stored in cents --}}
                <flux:subheading class="mb-2">${{ number_format($product->price / 100, 2) }}</flux:subheading>
                
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">{{ $product->description }}</p>
                
                <div class="mt-auto">
                    <flux:button href="/products/{{ $product->id }}" class="w-full">View Details</flux:button>
                </div>
            </flux:card>
        @empty
            <p>No products available right now. Check back soon!</p>
        @endforelse
    </div>
</div>