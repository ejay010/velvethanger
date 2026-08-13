<?php

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Collection;

new class extends Component
{
    /**
     * Computed property to fetch products.
     * We use a computed property so it is automatically cached during the request.
     */
    public function getProductsProperty(): Collection
    {
        // Only fetch products that are marked as active
        return Product::where('is_active', true)->get();
    }
};
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <flux:heading size="xl" class="mb-6">Welcome to Velvet Hanger Online Boutique</flux:heading>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Loop through the active products we fetched above --}}
        @forelse($this->products as $product)
            <flux:card>
                <flux:heading size="lg">{{ $product->name }}</flux:heading>
                
                {{-- Price is stored in cents, so we divide by 100 to show dollars --}}
                <flux:subheading class="mb-2">${{ number_format($product->price / 100, 2) }}</flux:subheading>
                
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $product->description }}</p>
                
                <div class="mt-auto">
                    {{-- Button to view product details. We'll set up the route for this later. --}}
                    <flux:button href="/products/{{ $product->id }}">View Details</flux:button>
                </div>
            </flux:card>
        @empty
            <p>No products available right now. Check back soon!</p>
        @endforelse
    </div>
</div>