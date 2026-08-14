<?php

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('creates a product with multiple variants and variant images', function () {
    Storage::fake('public');

    $category = Category::factory()->create();

    $v1Image1 = UploadedFile::fake()->image('rose_gold_1.jpg');
    $v1Image2 = UploadedFile::fake()->image('rose_gold_2.jpg');

    Livewire::test('admin.product-manager')
        ->set('name', 'Silk Cocktail Dress')
        ->set('category_id', $category->id)
        ->set('price', 12000)
        ->set('stock_quantity', 0)
        ->set('has_variants', true)
        ->set('new_variants', [
            ['name' => 'Small / Rose Gold', 'sku' => 'VH-DR-S-RGLD', 'price' => 12500, 'stock_quantity' => 8],
            ['name' => 'Medium / Black', 'sku' => 'VH-DR-M-BLK', 'price' => 12000, 'stock_quantity' => 12],
        ])
        ->set('variant_images.0', [$v1Image1, $v1Image2])

        ->call('createProduct')
        ->assertHasNoErrors();

    $product = Product::where('name', 'Silk Cocktail Dress')->first();

    expect($product)->not->toBeNull();
    expect($product->has_variants)->toBeTrue();
    expect($product->variants)->toHaveCount(2);

    $roseGoldVariant = $product->variants->firstWhere('name', 'Small / Rose Gold');
    expect($roseGoldVariant)->not->toBeNull();
    expect($roseGoldVariant->stock_quantity)->toBe(8);
    expect($roseGoldVariant->price)->toBe(12500);
    expect($roseGoldVariant->images)->toHaveCount(2);

    expect($product->total_stock)->toBe(20);
});

it('allows editing product variants and adding new options', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variant = $product->variants()->create([
        'name' => 'Small',
        'stock_quantity' => 5,
    ]);

    Livewire::test('admin.product-manager')
        ->call('editProduct', $product->id)
        ->call('updateVariant', $variant->id, 'Small / Emerald', 'SKU-EM-S', 9500, 15, true)
        ->assertHasNoErrors();

    $variant->refresh();
    expect($variant->name)->toBe('Small / Emerald');
    expect($variant->stock_quantity)->toBe(15);
    expect($variant->price)->toBe(9500);

    // Add another variant
    Livewire::test('admin.product-manager')
        ->call('editProduct', $product->id)
        ->set('add_variant_name', 'Large / Emerald')
        ->set('add_variant_stock', 10)
        ->call('addVariantToEditingProduct')
        ->assertHasNoErrors();

    $product->refresh();
    expect($product->variants)->toHaveCount(2);
});

it('allows storefront customers to select variants, view variant gallery, and add variant to cart', function () {
    Storage::fake('public');

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Summer Linen Shirt',
        'price' => 4500,
    ]);

    $variant = $product->variants()->create([
        'name' => 'Medium / Sky Blue',
        'price' => 4900,
        'stock_quantity' => 6,
    ]);

    $variantImage = UploadedFile::fake()->image('sky_blue.jpg');
    $path = $variantImage->store('products', 'public');
    $variant->images()->create([
        'product_id' => $product->id,
        'image_path' => $path,
        'is_featured' => true,
    ]);

    Livewire::test('storefront.product-show', ['product' => $product])
        ->assertStatus(200)
        ->assertSee('Medium / Sky Blue')
        ->set('selected_variant_id', $variant->id)
        ->call('addToCart');

    $cartService = app(CartService::class);
    $items = $cartService->getItems();

    expect($items)->not->toBeEmpty();
    $cartKey = "{$product->id}:{$variant->id}";
    expect($items)->toHaveKey($cartKey);
    expect($items[$cartKey]['variant_name'])->toBe('Medium / Sky Blue');
    expect($items[$cartKey]['price'])->toBe(4900);
});
