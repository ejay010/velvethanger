<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
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
    public int $stock_quantity = 0; // Available inventory for base product
    public bool $is_active = true;

    // Foreign key relationship property selected from category dropdown
    public string $category_id = '';

    // Main product file uploads
    public $new_images = [];
    public int $featured_image_index = 0;

    // Product Variations Creation State
    public bool $has_variants = false;
    public array $new_variants = [];
    public array $variant_images = [];

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

    // Add Variant to Existing Product (Edit Modal)
    public string $add_variant_name = '';
    public string $add_variant_sku = '';
    public ?int $add_variant_price = null;
    public int $add_variant_stock = 0;
    public $add_variant_images = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'description' => 'nullable|string',
        'price' => 'required|integer|min:0',
        'stock_quantity' => 'required|integer|min:0',
        'is_active' => 'boolean',
        'new_images.*' => 'image|max:2048',
    ];

    public function mount()
    {
        // Start with one empty variant row by default if variants are toggled on
        $this->addVariantRow();
    }

    public function addVariantRow()
    {
        $this->new_variants[] = [
            'name' => '',
            'sku' => '',
            'price' => null,
            'stock_quantity' => 0,
        ];
    }

    public function removeVariantRow(int $index)
    {
        unset($this->new_variants[$index]);
        unset($this->variant_images[$index]);
        $this->new_variants = array_values($this->new_variants);
        $this->variant_images = array_values($this->variant_images);
    }

    // Computed property to fetch all products with categories, images, and variants
    public function getProductsProperty()
    {
        return Product::with(['category', 'images', 'featuredImage', 'variants', 'variants.images'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Computed property to fetch all categories
    public function getCategoriesProperty()
    {
        return Category::orderBy('name', 'asc')->get();
    }

    // Computed property to fetch current editing product
    public function getEditingProductProperty()
    {
        if (! $this->editingProductId) {
            return null;
        }

        return Product::with(['category', 'images', 'featuredImage', 'variants', 'variants.images'])->find($this->editingProductId);
    }

    public function createProduct()
    {
        $this->validate();

        // 1. Create base product record
        $product = Product::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'category_id' => $this->category_id,
            'description' => $this->description,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'is_active' => $this->is_active,
        ]);

        // 2. Handle main product image uploads
        if (!empty($this->new_images)) {
            $featuredImagePath = null;

            foreach ($this->new_images as $index => $imageFile) {
                $path = $imageFile->store('products', 'public');
                $isFeatured = ((int) $index === (int) $this->featured_image_index);

                $product->images()->create([
                    'image_path' => $path,
                    'is_featured' => $isFeatured,
                ]);

                if ($isFeatured) {
                    $featuredImagePath = $path;
                }
            }

            if ($featuredImagePath) {
                $product->update(['image_url' => $featuredImagePath]);
            }
        }

        // 3. Handle Product Variants if toggled on
        if ($this->has_variants && !empty($this->new_variants)) {
            foreach ($this->new_variants as $vIndex => $varData) {
                if (empty(trim($varData['name'] ?? ''))) {
                    continue;
                }

                $variant = $product->variants()->create([
                    'name' => $varData['name'],
                    'sku' => !empty($varData['sku']) ? $varData['sku'] : null,
                    'price' => (!empty($varData['price']) && $varData['price'] > 0) ? (int) $varData['price'] : null,
                    'stock_quantity' => (int) ($varData['stock_quantity'] ?? 0),
                    'is_active' => true,
                ]);

                // Handle variant-specific images if uploaded
                if (isset($this->variant_images[$vIndex]) && !empty($this->variant_images[$vIndex])) {
                    foreach ($this->variant_images[$vIndex] as $imgIdx => $vImageFile) {
                        $vPath = $vImageFile->store('products', 'public');
                        $isVFeatured = ($imgIdx === 0);

                        ProductImage::create([
                            'product_id' => $product->id,
                            'product_variant_id' => $variant->id,
                            'image_path' => $vPath,
                            'is_featured' => $isVFeatured,
                        ]);
                    }
                }
            }
        }

        $this->reset(['name', 'category_id', 'description', 'price', 'stock_quantity', 'is_active', 'new_images', 'featured_image_index', 'has_variants', 'new_variants', 'variant_images']);
        $this->addVariantRow();

        Flux::toast('Product created successfully.', variant: 'success');
    }

    public function editProduct(int $productId)
    {
        $product = Product::with(['images', 'featuredImage', 'variants', 'variants.images'])->findOrFail($productId);

        $this->editingProductId = $product->id;
        $this->edit_name = $product->name;
        $this->edit_category_id = (string) $product->category_id;
        $this->edit_description = $product->description ?? '';
        $this->edit_price = $product->price;
        $this->edit_stock_quantity = $product->stock_quantity;
        $this->edit_is_active = $product->is_active;
        $this->edit_new_images = [];

        // Reset add variant fields
        $this->add_variant_name = '';
        $this->add_variant_sku = '';
        $this->add_variant_price = null;
        $this->add_variant_stock = 0;
        $this->add_variant_images = [];

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

        // Upload any main product images added during editing
        if (! empty($this->edit_new_images)) {
            $hasFeaturedAlready = $product->images()->whereNull('product_variant_id')->where('is_featured', true)->exists();

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

    public function addVariantToEditingProduct()
    {
        if (! $this->editingProductId) {
            return;
        }

        $this->validate([
            'add_variant_name' => 'required|string|max:255',
            'add_variant_stock' => 'required|integer|min:0',
            'add_variant_images.*' => 'image|max:2048',
        ]);

        $product = Product::findOrFail($this->editingProductId);

        $variant = $product->variants()->create([
            'name' => $this->add_variant_name,
            'sku' => !empty($this->add_variant_sku) ? $this->add_variant_sku : null,
            'price' => (!empty($this->add_variant_price) && $this->add_variant_price > 0) ? (int) $this->add_variant_price : null,
            'stock_quantity' => (int) $this->add_variant_stock,
            'is_active' => true,
        ]);

        if (!empty($this->add_variant_images)) {
            foreach ($this->add_variant_images as $index => $vImageFile) {
                $path = $vImageFile->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'image_path' => $path,
                    'is_featured' => ($index === 0),
                ]);
            }
        }

        $this->reset(['add_variant_name', 'add_variant_sku', 'add_variant_price', 'add_variant_stock', 'add_variant_images']);

        Flux::toast('Variant added successfully.', variant: 'success');
    }

    public function updateVariant(int $variantId, string $name, ?string $sku, ?int $price, int $stock, bool $isActive)
    {
        $variant = ProductVariant::findOrFail($variantId);
        $variant->update([
            'name' => $name,
            'sku' => !empty($sku) ? $sku : null,
            'price' => ($price !== null && $price > 0) ? $price : null,
            'stock_quantity' => max(0, $stock),
            'is_active' => $isActive,
        ]);

        Flux::toast('Variant updated.');
    }

    public function deleteVariant(int $variantId)
    {
        $variant = ProductVariant::findOrFail($variantId);
        
        // Delete variant images from disk
        foreach ($variant->images as $img) {
            if (Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
        }

        $variant->delete();

        Flux::toast('Variant deleted.');
    }

    public function setFeaturedImage(int $imageId)
    {
        if (! $this->editingProductId) {
            return;
        }

        $product = Product::findOrFail($this->editingProductId);
        $product->images()->whereNull('product_variant_id')->update(['is_featured' => false]);

        $image = ProductImage::where('product_id', $product->id)->findOrFail($imageId);
        $image->update(['is_featured' => true]);

        $product->update(['image_url' => $image->image_path]);

        Flux::toast('Featured image updated.');
    }

    public function setVariantFeaturedImage(int $variantId, int $imageId)
    {
        $variant = ProductVariant::findOrFail($variantId);
        $variant->images()->update(['is_featured' => false]);

        $image = ProductImage::where('product_variant_id', $variant->id)->findOrFail($imageId);
        $image->update(['is_featured' => true]);

        Flux::toast('Variant featured image updated.');
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
            $nextImage = $product->images()->whereNull('product_variant_id')->first();
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
                    <flux:input type="number" label="Base Price (in cents)" wire:model="price" required />

                    {{-- Stock Quantity Available for Sale --}}
                    <flux:input type="number" label="Base Stock Quantity" wire:model="stock_quantity" min="0" required />

                    {{-- Main Product Images Upload --}}
                    <div>
                        <flux:input type="file" label="Product Images" wire:model="new_images" multiple
                            accept="image/jpeg,image/png,image/gif,image/jpg,image/webp,image/avif"
                            hint="Upload main product images" />

                        @if (!empty($new_images))
                            <div class="mt-3">
                                <flux:label class="mb-2">Select Featured Image:</flux:label>
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

                    {{-- Toggle Product Variations --}}
                    <div class="border-t pt-4 border-gray-200 dark:border-gray-700">
                        <flux:checkbox label="Has Product Variations (Sizes, Colors, Scents)?" wire:model.live="has_variants" />

                        @if ($has_variants)
                            <div class="mt-4 space-y-4">
                                <flux:label>Product Options / Variations:</flux:label>
                                
                                @foreach ($new_variants as $index => $variantRow)
                                    <div class="p-3 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg space-y-2">
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs font-semibold text-gray-500">Option #{{ $index + 1 }}</span>
                                            @if (count($new_variants) > 1)
                                                <button type="button" wire:click="removeVariantRow({{ $index }})" class="text-xs text-red-500 hover:underline">Remove</button>
                                            @endif
                                        </div>

                                        <flux:input label="Option Name (e.g. Small / Rose Gold)" wire:model="new_variants.{{ $index }}.name" placeholder="Size / Color" required />
                                        
                                        <div class="grid grid-cols-2 gap-2">
                                            <flux:input label="SKU (Optional)" wire:model="new_variants.{{ $index }}.sku" placeholder="VH-001" />
                                            <flux:input type="number" label="Price Override (cents)" wire:model="new_variants.{{ $index }}.price" placeholder="Same as base" />
                                        </div>

                                        <flux:input type="number" label="Stock Quantity" wire:model="new_variants.{{ $index }}.stock_quantity" min="0" required />

                                        {{-- Multi-Image Upload for Variant --}}
                                        <div>
                                            <flux:input type="file" label="Variant Specific Photos" wire:model="variant_images.{{ $index }}" multiple accept="image/*" />
                                        </div>
                                    </div>
                                @endforeach

                                <flux:button type="button" size="sm" variant="ghost" wire:click="addVariantRow" class="w-full">
                                    + Add Another Option
                                </flux:button>
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
                                    <th class="py-2">Total Stock</th>
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
                                            @if ($product->has_variants)
                                                <flux:badge color="indigo" size="sm" class="mt-0.5">
                                                    {{ $product->variants->count() }} Variations
                                                </flux:badge>
                                            @elseif ($product->images->count() > 1)
                                                <span class="text-xs text-gray-500">{{ $product->images->count() }} images</span>
                                            @endif
                                        </td>
                                        <td class="py-3 font-medium">
                                            <flux:badge color="zinc">{{ $product->category->name ?? 'Uncategorized' }}</flux:badge>
                                        </td>
                                        <td class="py-3">${{ number_format($product->price / 100, 2) }}</td>
                                        <td class="py-3 font-medium">
                                            @if ($product->total_stock > 0)
                                                <flux:badge color="zinc">{{ $product->total_stock }} in stock</flux:badge>
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
    <flux:modal wire:model="showEditModal" class="max-w-3xl">
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
                    <flux:input type="number" label="Base Price (in cents)" wire:model="edit_price" required />
                    <flux:input type="number" label="Base Stock Quantity" wire:model="edit_stock_quantity" min="0" required />
                </div>

                <flux:checkbox label="Is Active?" wire:model="edit_is_active" />

                {{-- Main Product Images --}}
                <div class="border-t pt-4 border-gray-200 dark:border-gray-700">
                    <flux:label class="mb-2">Main Product Images:</flux:label>
                    @php
                        $mainImages = $this->editingProduct->images->whereNull('product_variant_id');
                    @endphp
                    @if ($mainImages->isEmpty())
                        <p class="text-sm text-gray-500 mb-3">No main product images uploaded yet.</p>
                    @else
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            @foreach ($mainImages as $img)
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

                    <flux:input type="file" label="Add Main Product Images" wire:model="edit_new_images" multiple accept="image/*" />
                </div>

                {{-- Existing Product Variants & Options Manager --}}
                <div class="border-t pt-4 border-gray-200 dark:border-gray-700 space-y-4">
                    <flux:heading size="md">Product Variations & Option Galleries</flux:heading>

                    @if ($this->editingProduct->variants->isEmpty())
                        <p class="text-sm text-gray-500">No variations configured for this product yet.</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($this->editingProduct->variants as $variant)
                                <div class="p-4 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg space-y-3"
                                    x-data="{ 
                                        vName: '{{ addslashes($variant->name) }}',
                                        vSku: '{{ addslashes($variant->sku ?? '') }}',
                                        vPrice: {{ $variant->price ?? 'null' }},
                                        vStock: {{ $variant->stock_quantity }},
                                        vActive: {{ $variant->is_active ? 'true' : 'false' }}
                                    }">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-sm">Variant Option: {{ $variant->name }}</span>
                                        <div class="flex gap-2">
                                            <flux:button size="xs" type="button" 
                                                @click="$wire.updateVariant({{ $variant->id }}, vName, vSku, vPrice, vStock, vActive)">
                                                Save Option
                                            </flux:button>
                                            <flux:button size="xs" variant="danger" type="button" wire:click="deleteVariant({{ $variant->id }})">
                                                Delete
                                            </flux:button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <flux:input label="Name" x-model="vName" />
                                        <flux:input label="SKU" x-model="vSku" />
                                        <flux:input type="number" label="Price Override (cents)" x-model="vPrice" />
                                        <flux:input type="number" label="Stock Quantity" x-model="vStock" />
                                    </div>

                                    {{-- Multi-Images for this specific Variant --}}
                                    <div class="border-t pt-2 border-zinc-200 dark:border-zinc-700">
                                        <span class="text-xs font-medium text-gray-500 mb-2 block">Variant Gallery Photos:</span>
                                        @if ($variant->images->isEmpty())
                                            <p class="text-xs text-gray-400 mb-2">Using main product images.</p>
                                        @else
                                            <div class="grid grid-cols-4 gap-2 mb-2">
                                                @foreach ($variant->images as $vImg)
                                                    <div class="relative rounded overflow-hidden border p-0.5 bg-white dark:bg-zinc-800">
                                                        <img src="{{ Storage::url($vImg->image_path) }}" class="w-full h-14 object-cover rounded" />
                                                        <div class="mt-1 flex flex-col gap-0.5 text-center">
                                                            @if ($vImg->is_featured)
                                                                <span class="text-[10px] text-indigo-600 font-bold">Featured</span>
                                                            @else
                                                                <button type="button" wire:click="setVariantFeaturedImage({{ $variant->id }}, {{ $vImg->id }})" class="text-[10px] text-indigo-500 hover:underline">Set Featured</button>
                                                            @endif
                                                            <button type="button" wire:click="deleteImage({{ $vImg->id }})" class="text-[10px] text-red-500 hover:underline">Delete</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Add New Variant Form --}}
                    <div class="border-t pt-4 border-gray-200 dark:border-gray-700 space-y-3">
                        <span class="text-sm font-semibold">Add New Variation to Product:</span>
                        <div class="grid grid-cols-2 gap-3">
                            <flux:input label="Option Name" wire:model="add_variant_name" placeholder="e.g. Large / Rose Gold" />
                            <flux:input label="SKU (Optional)" wire:model="add_variant_sku" placeholder="SKU-001" />
                            <flux:input type="number" label="Price Override (cents)" wire:model="add_variant_price" placeholder="Leave empty for base price" />
                            <flux:input type="number" label="Stock Quantity" wire:model="add_variant_stock" min="0" />
                        </div>
                        <flux:input type="file" label="Variant Photos" wire:model="add_variant_images" multiple accept="image/*" />

                        <flux:button type="button" size="sm" variant="primary" wire:click="addVariantToEditingProduct">
                            + Add Variation
                        </flux:button>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t pt-4 border-gray-200 dark:border-gray-700">
                    <flux:button type="button" variant="ghost" wire:click="$set('showEditModal', false)">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save Product Changes</flux:button>
                </div>
            </form>
        @endif
    </flux:modal>
</div>





