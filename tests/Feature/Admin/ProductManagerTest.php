<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('renders product manager component with existing categories', function () {
    $category = Category::factory()->create(['name' => 'Handbags']);

    Livewire::test('admin.product-manager')
        ->assertStatus(200)
        ->assertSee('Handbags');
});

it('creates a product with an assigned category and stock quantity', function () {
    $category = Category::factory()->create(['name' => 'Jewelry']);

    Livewire::test('admin.product-manager')
        ->set('name', 'Gold Necklace')
        ->set('category_id', $category->id)
        ->set('description', 'Elegant 14k gold necklace.')
        ->set('price', 4500)
        ->set('stock_quantity', 15)
        ->set('is_active', true)
        ->call('createProduct')
        ->assertHasNoErrors()
        ->assertSet('name', '')
        ->assertSet('category_id', '')
        ->assertSet('stock_quantity', 0);

    $product = Product::where('name', 'Gold Necklace')->first();

    expect($product)->not->toBeNull();
    expect($product->category_id)->toBe($category->id);
    expect($product->stock_quantity)->toBe(15);
});

it('requires category_id to create a product', function () {
    Livewire::test('admin.product-manager')
        ->set('name', 'Silver Ring')
        ->set('category_id', '')
        ->call('createProduct')
        ->assertHasErrors(['category_id' => 'required']);
});

it('loads product data into edit modal', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Original Name',
        'price' => 2000,
        'stock_quantity' => 5,
    ]);

    Livewire::test('admin.product-manager')
        ->call('editProduct', $product->id)
        ->assertSet('showEditModal', true)
        ->assertSet('edit_name', 'Original Name')
        ->assertSet('edit_price', 2000)
        ->assertSet('edit_stock_quantity', 5);
});

it('updates an existing product record', function () {
    $category = Category::factory()->create();
    $newCategory = Category::factory()->create(['name' => 'Shoes']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Old Shoes',
        'price' => 5000,
        'stock_quantity' => 10,
    ]);

    Livewire::test('admin.product-manager')
        ->call('editProduct', $product->id)
        ->set('edit_name', 'Leather Boots')
        ->set('edit_category_id', $newCategory->id)
        ->set('edit_price', 8500)
        ->set('edit_stock_quantity', 25)
        ->call('updateProduct')
        ->assertHasNoErrors()
        ->assertSet('showEditModal', false);

    $product->refresh();
    expect($product->name)->toBe('Leather Boots');
    expect($product->slug)->toBe('leather-boots');
    expect($product->category_id)->toBe($newCategory->id);
    expect($product->price)->toBe(8500);
    expect($product->stock_quantity)->toBe(25);
});

it('allows managing images on an existing product', function () {
    Storage::fake('public');

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $file1 = UploadedFile::fake()->image('img1.jpg');
    $file2 = UploadedFile::fake()->image('img2.jpg');

    Livewire::test('admin.product-manager')
        ->call('editProduct', $product->id)
        ->set('edit_new_images', [$file1, $file2])
        ->call('updateProduct')
        ->assertHasNoErrors();

    $product->refresh();
    expect($product->images)->toHaveCount(2);

    $img1 = $product->images->first();
    $img2 = $product->images->last();

    // Change featured image to image 2
    Livewire::test('admin.product-manager')
        ->call('editProduct', $product->id)
        ->call('setFeaturedImage', $img2->id);

    $img2->refresh();
    expect($img2->is_featured)->toBeTrue();

    // Delete image 1
    Livewire::test('admin.product-manager')
        ->call('editProduct', $product->id)
        ->call('deleteImage', $img1->id);

    $product->refresh();
    expect($product->images)->toHaveCount(1);
});
