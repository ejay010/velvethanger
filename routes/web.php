<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('shop.home');
})->name('home');

Route::get('/products/{product}', function (Product $product) {
    return view('shop.product', ['product' => $product]);
})->name('product.show');

Route::get('/cart', function () {
    return view('shop.cart');
})->name('cart');

Route::get('/checkout', function () {
    return view('shop.checkout');
})->name('checkout');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/products', function () {
        return view('admin.products');
    })->name('products');

    Route::get('/orders', function () {
        return view('admin.orders');
    })->name('orders');

    Route::get('/category', function () {
        return view('admin.category');
    })->name('category');
});

require __DIR__.'/settings.php';
