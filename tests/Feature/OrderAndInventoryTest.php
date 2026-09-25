<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('checkout decrements stock for products and variants', function () {
    // 1. Arrange: Create a product and a product variant with known stock
    $product = Product::factory()->create([
        'name' => 'Linen Dress',
        'price' => 6000,
        'stock_quantity' => 10,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Size M / Pink',
        'price' => 6500,
        'stock_quantity' => 5,
        'is_active' => true,
    ]);

    $cartItems = [
        [
            'key' => "{$product->id}:{$variant->id}",
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'quantity' => 2,
            'price' => 6500,
            'image_url' => null,
        ],
    ];

    // 2. Act: Process the checkout
    $orderService = app(OrderService::class);
    $order = $orderService->processCheckout(
        userId: null,
        cartItems: $cartItems,
        totalAmount: 13000,
        customerDetails: [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'address' => 'Nassau, Bahamas',
        ]
    );

    // 3. Assert: Order item created with variant details, and variant stock is decremented
    expect($order)->toBeInstanceOf(Order::class);
    expect($variant->fresh()->stock_quantity)->toBe(3); // 5 - 2 = 3

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'variant_name' => 'Size M / Pink',
        'quantity' => 2,
    ]);
});

test('checkout throws an exception when an item is out of stock', function () {
    $product = Product::factory()->create([
        'name' => 'Silk Scarf',
        'price' => 2000,
        'stock_quantity' => 1,
    ]);

    $cartItems = [
        [
            'key' => "{$product->id}:0",
            'product_id' => $product->id,
            'variant_id' => null,
            'product_name' => $product->name,
            'variant_name' => null,
            'quantity' => 3, // Requesting more than in stock
            'price' => 2000,
            'image_url' => null,
        ],
    ];

    $orderService = app(OrderService::class);

    expect(fn () => $orderService->processCheckout(
        userId: null,
        cartItems: $cartItems,
        totalAmount: 6000,
        customerDetails: ['name' => 'Jane', 'email' => 'jane@example.com', 'address' => 'Nassau']
    ))->toThrow(Exception::class);

    // Stock should not be changed due to transaction rollback
    expect($product->fresh()->stock_quantity)->toBe(1);
    expect(Order::count())->toBe(0);
});

test('cancelling an order restores inventory to products and variants', function () {
    $product = Product::factory()->create(['stock_quantity' => 8]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'stock_quantity' => 4,
    ]);

    $order = Order::create([
        'status' => 'pending',
        'total_amount' => 10000,
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'variant_name' => 'Small',
        'quantity' => 2,
        'unit_price' => 5000,
    ]);

    $orderService = app(OrderService::class);
    $orderService->cancelOrder($order);

    expect($order->fresh()->status)->toBe('cancelled');
    expect($variant->fresh()->stock_quantity)->toBe(6); // 4 + 2 = 6
});

test('guest can track their order using order id and email', function () {
    $order = Order::create([
        'status' => 'pending',
        'total_amount' => 4500,
        'customer_name' => 'Alice Bahamas',
        'customer_email' => 'alice@example.com',
        'customer_address' => 'Cable Beach, Nassau',
    ]);

    $product = Product::factory()->create(['name' => 'Island Candle']);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 4500,
    ]);

    Livewire::test('storefront.order-lookup')
        ->set('order_id', (string) $order->id)
        ->set('email', 'alice@example.com')
        ->call('lookupOrder')
        ->assertSee('Order Placed')
        ->assertSee('Island Candle')
        ->assertSee('Cable Beach, Nassau');
});

test('authenticated user can view their order history', function () {
    $user = User::factory()->create(['email' => 'customer@example.com']);

    $order = Order::create([
        'user_id' => $user->id,
        'status' => 'ready_for_pickup',
        'total_amount' => 7500,
        'customer_name' => $user->name,
        'customer_email' => $user->email,
    ]);

    $product = Product::factory()->create(['name' => 'Velvet Jumpsuit']);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 7500,
    ]);

    $this->actingAs($user)
        ->get(route('customer.orders'))
        ->assertStatus(200)
        ->assertSee('My Orders')
        ->assertSee('Ready for Pickup')
        ->assertSee('Velvet Jumpsuit');
});

test('admin can transition order to ready for pickup and cancel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $product = Product::factory()->create(['stock_quantity' => 10]);
    $order = Order::create([
        'status' => 'pending',
        'total_amount' => 5000,
        'customer_name' => 'Bob',
        'customer_email' => 'bob@example.com',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 2500,
    ]);

    $this->actingAs($admin);

    Livewire::test('admin.order-manager')
        ->call('updateStatus', $order->id, 'ready_for_pickup')
        ->assertSee('Ready for Pickup');

    expect($order->fresh()->status)->toBe('ready_for_pickup');

    Livewire::test('admin.order-manager')
        ->call('updateStatus', $order->id, 'cancelled');

    expect($order->fresh()->status)->toBe('cancelled');
    expect($product->fresh()->stock_quantity)->toBe(12); // 10 + 2 = 12 restored
});
