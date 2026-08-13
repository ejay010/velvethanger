<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);
it('loads the home page and displays products', function () {
    // Arrange: Create some products
    $activeProduct = Product::factory()->create([
        'name' => 'Active Dress',
        'slug' => 'active-dress',
        'is_active' => true,
        'price' => 5000,
    ]);

    $inactiveProduct = Product::factory()->create([
        'name' => 'Hidden Shoes',
        'slug' => 'hidden-shoes',
        'is_active' => false,
        'price' => 3000,
    ]);

    // Act: Visit the home page
    $response = get('/');

    // Assert: Check that it's successful and shows only the active product
    $response->assertStatus(200);
    $response->assertSee('Active Dress');
    $response->assertDontSee('Hidden Shoes');
});

it('loads a single product page', function () {
    $product = Product::factory()->create([
        'name' => 'Test Necklace',
        'slug' => 'test-necklace',
        'is_active' => true,
        'price' => 2500,
    ]);

    $response = get(route('product.show', $product));

    $response->assertStatus(200);
    $response->assertSee('Test Necklace');
    $response->assertSee('$25.00'); // Formatting check
});

it('loads the cart page', function () {
    $response = get('/cart');
    $response->assertStatus(200);
    $response->assertSee('Your Shopping Cart');
});

it('loads the checkout page', function () {
    $response = get('/checkout');
    $response->assertStatus(200);
    $response->assertSee('Checkout');
});
