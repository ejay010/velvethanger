<?php

use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

it('renders product manager component with existing categories', function () {
    $category = Category::factory()->create(['name' => 'Handbags']);

    Livewire::test('admin.product-manager')
        ->assertStatus(200)
        ->assertSee('Handbags');
});

it('creates a product with an assigned category', function () {
    $category = Category::factory()->create(['name' => 'Jewelry']);

    Livewire::test('admin.product-manager')
        ->set('name', 'Gold Necklace')
        ->set('category_id', $category->id)
        ->set('description', 'Elegant 14k gold necklace.')
        ->set('price', 4500)
        ->set('is_active', true)
        ->call('createProduct')
        ->assertHasNoErrors()
        ->assertSet('name', '')
        ->assertSet('category_id', '');

    $product = Product::where('name', 'Gold Necklace')->first();

    expect($product)->not->toBeNull();
    expect($product->category_id)->toBe($category->id);
    expect($product->category->name)->toBe('Jewelry');
});

it('requires category_id to create a product', function () {
    Livewire::test('admin.product-manager')
        ->set('name', 'Silver Ring')
        ->set('category_id', '')
        ->call('createProduct')
        ->assertHasErrors(['category_id' => 'required']);
});
