<?php

use Livewire\Component;
use App\Models\Product;
use Flux\Flux;

new class extends Component {
    // Properties for creating a new product
    public string $name = '';
    public string $description = '';
    public int $price = 0; // Stored in cents
    public bool $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|integer|min:0',
        'is_active' => 'boolean',
    ];

    // Computed property to fetch all products
    public function getProductsProperty()
    {
        return Product::orderBy('created_at', 'desc')->get();
    }

    public function createProduct()
    {
        $this->validate();

        Product::create([
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'is_active' => $this->is_active,
        ]);

        $this->reset(['name', 'description', 'price', 'is_active']);

        // Flux provides a toast helper to show success messages
        Flux::toast('Product created successfully.', variant: 'success');
    }

    public function toggleActive(int $productId)
    {
        $product = Product::findOrFail($productId);
        $product->update(['is_active' => !$product->is_active]);
        Flux::toast('Product status updated.');
    }
};
?>

<div class="max-w-6xl mx-auto py-8 px-4">
    <flux:heading size="xl" class="mb-6">Product Manager</flux:heading>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        {{-- Form to create a new product --}}
        <div class="md:col-span-1">
            <flux:card>
                <flux:heading size="lg" class="mb-4">Add New Product</flux:heading>
                <form wire:submit.prevent="createProduct" class="space-y-4">
                    <flux:input label="Product Name" wire:model="name" required />

                    <flux:textarea label="Description" wire:model="description" />

                    {{-- Prices are stored in cents, but for simplicity we input cents here.
                         In a full app, you might want a custom input to format dollars. --}}
                    <flux:input type="number" label="Price (in cents)" wire:model="price" required />

                    <flux:checkbox label="Is Active?" wire:model="is_active" />

                    <flux:button type="submit" variant="primary" class="w-full">Create Product</flux:button>
                </form>
            </flux:card>
        </div>

        {{-- List of existing products --}}
        <div class="md:col-span-2">
            <flux:card>
                <flux:heading size="lg" class="mb-4">Existing Products</flux:heading>

                @if ($this->products->isEmpty())
                    <p class="text-gray-500">No products found.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2">ID</th>
                                    <th class="py-2">Name</th>
                                    <th class="py-2">Price</th>
                                    <th class="py-2">Status</th>
                                    <th class="py-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->products as $product)
                                    <tr class="border-b last:border-0">
                                        <td class="py-3 text-gray-500">#{{ $product->id }}</td>
                                        <td class="py-3 font-medium">{{ $product->name }}</td>
                                        <td class="py-3">${{ number_format($product->price / 100, 2) }}</td>
                                        <td class="py-3">
                                            @if ($product->is_active)
                                                <flux:badge color="green">Active</flux:badge>
                                            @else
                                                <flux:badge color="gray">Inactive</flux:badge>
                                            @endif
                                        </td>
                                        <td class="py-3 text-right">
                                            <flux:button size="sm" wire:click="toggleActive({{ $product->id }})">
                                                Toggle Status
                                            </flux:button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </flux:card>
        </div>

    </div>
</div>
