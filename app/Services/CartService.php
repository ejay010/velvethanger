<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Get all items currently in the cart session.
     */
    public function getItems(): array
    {
        return Session::get('cart', []);
    }

    /**
     * Add a product or variant to the cart.
     */
    public function add(int $productId, int $quantity = 1, int $price = 0, ?int $variantId = null, ?string $variantName = null, ?string $imageUrl = null): void
    {
        $cart = $this->getItems();

        $key = $variantId ? "{$productId}:{$variantId}" : "{$productId}:0";

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $product = Product::find($productId);
            $productName = $product ? $product->name : "Product #{$productId}";

            $cart[$key] = [
                'key' => $key,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'product_name' => $productName,
                'variant_name' => $variantName,
                'quantity' => $quantity,
                'price' => $price > 0 ? $price : ($product ? $product->price : 0),
                'image_url' => $imageUrl ?? ($product ? $product->featured_image_url : null),
            ];
        }

        Session::put('cart', $cart);
    }

    /**
     * Remove an item entirely from the cart by its unique item key.
     */
    public function remove(string $key): void
    {
        $cart = $this->getItems();

        if (isset($cart[$key])) {
            unset($cart[$key]);
            Session::put('cart', $cart);
        }
    }

    /**
     * Update the quantity of a specific item in the cart.
     */
    public function updateQuantity(string $key, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($key);

            return;
        }

        $cart = $this->getItems();

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = $quantity;
            Session::put('cart', $cart);
        }
    }

    /**
     * Clear the entire cart from the session.
     */
    public function clear(): void
    {
        Session::forget('cart');
    }

    /**
     * Calculate the total price of all items in the cart.
     */
    public function getTotal(): int
    {
        $cart = $this->getItems();
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['quantity'] * $item['price'];
        }

        return $total;
    }

    /**
     * Calculate the total number of items in the cart.
     */
    public function getTotalQuantity(): int
    {
        $cart = $this->getItems();
        $quantity = 0;

        foreach ($cart as $item) {
            $quantity += $item['quantity'];
        }

        return $quantity;
    }
}
