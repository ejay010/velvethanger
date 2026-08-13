<?php

namespace App\Services;

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
     * Add a product to the cart or increment its quantity if it already exists.
     */
    public function add(int $productId, int $quantity = 1, int $price = 0): void
    {
        $cart = $this->getItems();

        // If the product is already in the cart, increase the quantity
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            // Otherwise, add it to the cart
            $cart[$productId] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
            ];
        }

        Session::put('cart', $cart);
    }

    /**
     * Remove an item entirely from the cart.
     */
    public function remove(int $productId): void
    {
        $cart = $this->getItems();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
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
}
