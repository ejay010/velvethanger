<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

new class extends Component {
    use WithFileUploads;

    // Properties for creating a new product
    public string $name = '';
    public string $description = '';
    public int $price = 0; // Stored in cents
    public int $stock_quantity = 0; // Available inventory for sale
    public bool $is_active = true;

    // Foreign key relationship property selected from the category dropdown
    public string $category_id = '';

    // Multiple file uploads and index of the selected featured image
    public $new_images = [];
    public int $featured_image_index = 0;

    // Edit Modal State & Properties
    public bool $showEditModal = false;
    public ?int $editingProductId = null;
    public string $edit_name = '';
    public string $edit_category_id = '';
    public string $edit_description = '';
    public int $edit_price = 0;
    public int $edit_stock_quantity = 0;
    public bool $edit_is_active = true;
    public $edit_new_images = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'description' => 'nullable|string',
        'price' => 'required|integer|min:0',
        'stock_quantity' => 'required|integer|min:0',
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

    // Computed property to fetch the product currently being edited
    public function getEditingProductProperty()
    {
        if (! $this->editingProductId) {
            return null;
        }

        return Product::with(['category', 'images', 'featuredImage'])->find($this->editingProductId);
    }

    public function createProduct()
    {
        $this->validate();

        // 1. Create the base product record with stock_quantity
        $product = Product::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'category_id' => $this->category_id,
            'description' => $this->description,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
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

        $this->reset(['name', 'category_id', 'description', 'price', 'stock_quantity', 'is_active', 'new_images', 'featured_image_index']);

        Flux::toast('Product created successfully.', variant: 'success');
    }

    public function editProduct(int $productId)
    {
        $product = Product::with(['images', 'featuredImage'])->findOrFail($productId);

        $this->editingProductId = $product->id;
        $this->edit_name = $product->name;
        $this->edit_category_id = (string) $product->category_id;
        $this->edit_description = $product->description ?? '';
        $this->edit_price = $product->price;
        $this->edit_stock_quantity = $product->stock_quantity;
        $this->edit_is_active = $product->is_active;
        $this->edit_new_images = [];

        $this->showEditModal = true;
    }

    public function updateProduct()
    {
        $this->validate([
            'edit_name' => 'required|string|max:255',
            'edit_category_id' => 'required|exists:categories,id',
            'edit_description' => 'nullable|string',
            'edit_price' => 'required|integer|min:0',
            'edit_stock_quantity' => 'required|integer|min:0',
            'edit_is_active' => 'boolean',
            'edit_new_images.*' => 'image|max:2048',
        ]);

        $product = Product::findOrFail($this->editingProductId);

        $product->update([
            'name' => $this->edit_name,
            'slug' => Str::slug($this->edit_name),
            'category_id' => $this->edit_category_id,
            'description' => $this->edit_description,
            'price' => $this->edit_price,
            'stock_quantity' => $this->edit_stock_quantity,
            'is_active' => $this->edit_is_active,
        ]);

        // Upload any new images added during editing
        if (! empty($this->edit_new_images)) {
            $hasFeaturedAlready = $product->images()->where('is_featured', true)->exists();

            foreach ($this->edit_new_images as $index => $imageFile) {
                $path = $imageFile->store('products', 'public');
                $isFeatured = (! $hasFeaturedAlready && $index === 0);

                $product->images()->create([
                    'image_path' => $path,
                    'is_featured' => $isFeatured,
                ]);

                if ($isFeatured) {
                    $product->update(['image_url' => $path]);
                    $hasFeaturedAlready = true;
                }
            }
        }

        $this->reset(['edit_new_images']);
        $this->showEditModal = false;

        Flux::toast('Product updated successfully.', variant: 'success');
    }

    public function setFeaturedImage(int $imageId)
    {
        if (! $this->editingProductId) {
            return;
        }

        $product = Product::findOrFail($this->editingProductId);

        // Reset all images for this product to not featured
        $product->images()->update(['is_featured' => false]);

        // Set selected image as featured
        $image = ProductImage::where('product_id', $product->id)->findOrFail($imageId);
        $image->update(['is_featured' => true]);

        // Sync main image_url on products table
        $product->update(['image_url' => $image->image_path]);

        Flux::toast('Featured image updated.');
    }

    public function deleteImage(int $imageId)
    {
        $image = ProductImage::findOrFail($imageId);
        $product = Product::findOrFail($image->product_id);

        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $wasFeatured = $image->is_featured;
        $image->delete();

        if ($wasFeatured) {
            $nextImage = $product->images()->first();
            if ($nextImage) {
                $nextImage->update(['is_featured' => true]);
                $product->update(['image_url' => $nextImage->image_path]);
            } else {
                $product->update(['image_url' => null]);
            }
        }

        Flux::toast('Image deleted.');
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

                    {{-- Stock Quantity Available for Sale --}}
                    <flux:input type="number" label="Stock Quantity (available for sale)" wire:model="stock_quantity" min="0" required />

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
                                    <th class="py-2">Stock</th>
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
                                        <td class="py-3 font-medium">
                                            @if ($product->stock_quantity > 0)
                                                <flux:badge color="zinc">{{ $product->stock_quantity }} in stock</flux:badge>
                                            @else
                                                <flux:badge color="red">Out of stock</flux:badge>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @if ($product->is_active)
                                                <flux:badge color="green">Active</flux:badge>
                                            @else
                                                <flux:badge color="gray">Inactive</flux:badge>
                                            @endif
                                        </td>
                                        <td class="py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <flux:button size="sm" icon="pencil-square" wire:click="editProduct({{ $product->id }})">
                                                    Edit
                                                </flux:button>
                                                <flux:button size="sm" wire:click="toggleActive({{ $product->id }})">
                                                    Toggle Status
                                                </flux:button>
                                            </div>
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

    {{-- Edit Product Modal --}}
    <flux:modal wire:model="showEditModal" class="max-w-2xl">
        @if ($this->editingProduct)
            <flux:heading size="lg" class="mb-4">Edit Product: {{ $this->editingProduct->name }}</flux:heading>

            <form wire:submit.prevent="updateProduct" class="space-y-4">
                <flux:input label="Product Name" wire:model="edit_name" required />

                <flux:select label="Category" wire:model="edit_category_id" required>
                    @foreach ($this->categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:textarea label="Description" wire:model="edit_description" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="number" label="Price (in cents)" wire:model="edit_price" required />
                    <flux:input type="number" label="Stock Quantity" wire:model="edit_stock_quantity" min="0" required />
                </div>

                <flux:checkbox label="Is Active?" wire:model="edit_is_active" />

                {{-- Existing Images Manager --}}
                <div class="border-t pt-4 border-gray-200 dark:border-gray-700">
                    <flux:label class="mb-2">Current Product Images:</flux:label>
                    @if ($this->editingProduct->images->isEmpty())
                        <p class="text-sm text-gray-500 mb-3">No images uploaded yet.</p>
                    @else
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            @foreach ($this->editingProduct->images as $img)
                                <div class="relative rounded-lg overflow-hidden border p-1 bg-zinc-50 dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700">
                                    <img src="{{ Storage::url($img->image_path) }}" class="w-full h-20 object-cover rounded" />
                                    <div class="mt-2 flex flex-col gap-1 text-center">
                                        @if ($img->is_featured)
                                            <flux:badge color="indigo" size="sm" class="justify-center">★ Featured</flux:badge>
                                        @else
                                            <flux:button size="xs" type="button" wire:click="setFeaturedImage({{ $img->id }})">
                                                Set Featured
                                            </flux:button>
                                        @endif
                                        <flux:button size="xs" variant="danger" type="button" wire:click="deleteImage({{ $img->id }})">
                                            Delete
                                        </flux:button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <flux:input type="file" label="Add Supporting Images" wire:model="edit_new_images" multiple
                        accept="image/jpeg,image/png,image/gif,image/jpg,image/webp,image/avif" />
                </div>

                <div class="flex justify-end gap-3 border-t pt-4 border-gray-200 dark:border-gray-700">
                    <flux:button type="button" variant="ghost" wire:click="$set('showEditModal', false)">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save Changes</flux:button>
                </div>
            </form>
        @endif
    </flux:modal>
</div>




