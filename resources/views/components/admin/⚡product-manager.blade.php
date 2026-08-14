<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Flux\Flux;

new class extends Component {
    use WithFileUploads;

    // Properties for creating a new product
    public string $name = '';
    public string $description = '';
    public int $price = 0; // Stored in cents
    public bool $is_active = true;

    // Foreign key relationship property selected from the category dropdown
    public string $category_id = '';

    // Multiple file uploads and index of the selected featured image
    public $new_images = [];
    public int $featured_image_index = 0;

    protected $rules = [
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'description' => 'nullable|string',
        'price' => 'required|integer|min:0',
        'is_active' => 'boolean',
        'new_images.*' => 'image|max:2048',
    ];

    // Computed property to fetch all products with their associated category and images
    public function getProductsProperty()
    {
        return Product::with(['category', 'images', 'featuredImage'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Computed property to fetch all available categories for the dropdown select
    public function getCategoriesProperty()
    {
        return Category::orderBy('name', 'asc')->get();
    }

    public function createProduct()
    {
        $this->validate();

        // 1. Create the base product record
        $product = Product::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'category_id' => $this->category_id,
            'description' => $this->description,
            'price' => $this->price,
            'is_active' => $this->is_active,
        ]);

        // 2. Handle image uploads if files were attached
        if (!empty($this->new_images)) {
            $featuredImagePath = null;

            foreach ($this->new_images as $index => $imageFile) {
                // Store file in storage/app/public/products
                $path = $imageFile->store('products', 'public');
                $isFeatured = ((int) $index === (int) $this->featured_image_index);

                // Create relationship record in product_images table
                $product->images()->create([
                    'image_path' => $path,
                    'is_featured' => $isFeatured,
                ]);

                if ($isFeatured) {
                    $featuredImagePath = $path;
                }
            }

            // Sync the main image_url column on products table if a featured image was set
            if ($featuredImagePath) {
                $product->update(['image_url' => $featuredImagePath]);
            }
        }

        $this->reset(['name', 'category_id', 'description', 'price', 'is_active', 'new_images', 'featured_image_index']);

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

                    {{-- Category Selection Dropdown --}}
                    <flux:select label="Category" wire:model="category_id" placeholder="Select a category..." required>
                        @foreach ($this->categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:textarea label="Description" wire:model="description" />

                    {{-- Prices are stored in cents --}}
                    <flux:input type="number" label="Price (in cents)" wire:model="price" required />

                    {{-- Multiple Images Upload --}}
                    <div>
                        <flux:input type="file" label="Product Images" wire:model="new_images" multiple
                            accept="image/jpeg,image/png,image/gif,image/jpg,image/webp,image/avif"
                            hint="Upload multiple images and select one as featured" />

                        {{-- Previews & Featured Image Selection --}}
                        @if (!empty($new_images))
                            <div class="mt-3">
                                <flux:label class="mb-2">Click to select Featured Image:</flux:label>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach ($new_images as $index => $img)
                                        <div wire:click="$set('featured_image_index', {{ $index }})"
                                            class="relative cursor-pointer rounded-lg overflow-hidden border-2 p-1 transition-all {{ $featured_image_index === $index ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/50' : 'border-gray-200 dark:border-gray-700 opacity-70 hover:opacity-100' }}">
                                            <img src="{{ $img->temporaryUrl() }}" class="w-full h-16 object-cover rounded" />
                                            <div class="mt-1 text-center">
                                                @if ($featured_image_index === $index)
                                                    <flux:badge color="indigo" size="sm">★ Featured</flux:badge>
                                                @else
                                                    <span class="text-xs text-gray-500">Select</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

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
                                    <th class="py-2">Image</th>
                                    <th class="py-2">Name</th>
                                    <th class="py-2">Category</th>
                                    <th class="py-2">Price</th>
                                    <th class="py-2">Status</th>
                                    <th class="py-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->products as $product)
                                    <tr class="border-b last:border-0">
                                        <td class="py-3">
                                            @if ($product->featured_image_url)
                                                <img src="{{ $product->featured_image_url }}" alt="{{ $product->name }}"
                                                    class="w-12 h-12 object-cover rounded-lg border border-gray-200 dark:border-gray-700" />
                                            @else
                                                <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center text-xs text-gray-400">
                                                    No Img
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 font-medium">
                                            <div>{{ $product->name }}</div>
                                            @if ($product->images->count() > 1)
                                                <span class="text-xs text-gray-500">{{ $product->images->count() }} images</span>
                                            @endif
                                        </td>
                                        <td class="py-3 font-medium">
                                            <flux:badge color="zinc">{{ $product->category->name ?? 'Uncategorized' }}</flux:badge>
                                        </td>
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


