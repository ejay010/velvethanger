<?php

namespace App\Services;

use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    /**
     * Process checkout from the given cart items.
     * Validates inventory, creates the order and line items, and decrements stock.
     *
     * @throws \Exception if an item is out of stock or requested quantity exceeds available stock.
     */
    public function processCheckout(?int $userId, array $cartItems, int $totalAmount, array $customerDetails = []): Order
    {
        // We use a database transaction so that if anything fails (e.g. out of stock), everything rollbacks safely
        return DB::transaction(function () use ($userId, $cartItems, $totalAmount, $customerDetails) {

            // 1. First Pass: Validate that all requested items are in stock
            foreach ($cartItems as $item) {
                $quantityRequested = (int) ($item['quantity'] ?? 1);

                if (! empty($item['variant_id'])) {
                    // Check stock for the specific product variant
                    $variant = ProductVariant::findOrFail($item['variant_id']);

                    if ($variant->stock_quantity < $quantityRequested) {
                        $variantTitle = $item['variant_name'] ? " ({$item['variant_name']})" : '';
                        throw new \Exception("Sorry, '{$item['product_name']}{$variantTitle}' only has {$variant->stock_quantity} item(s) left in stock.");
                    }
                } else {
                    // Check stock on the main product
                    $product = Product::findOrFail($item['product_id']);

                    if ($product->stock_quantity < $quantityRequested) {
                        throw new \Exception("Sorry, '{$item['product_name']}' only has {$product->stock_quantity} item(s) left in stock.");
                    }
                }
            }

            // 2. Create the Order record
            $order = Order::create([
                'user_id' => $userId,
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'customer_name' => $customerDetails['name'] ?? null,
                'customer_email' => $customerDetails['email'] ?? null,
                'customer_address' => $customerDetails['address'] ?? null,
            ]);

            // 3. Attach items to the order & Decrement inventory
            foreach ($cartItems as $item) {
                $quantityRequested = (int) ($item['quantity'] ?? 1);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'variant_name' => $item['variant_name'] ?? null,
                    'quantity' => $quantityRequested,
                    'unit_price' => $item['price'],
                ]);

                // Decrement inventory
                if (! empty($item['variant_id'])) {
                    $variant = ProductVariant::find($item['variant_id']);
                    $variant?->decrement('stock_quantity', $quantityRequested);
                } else {
                    $product = Product::find($item['product_id']);
                    $product?->decrement('stock_quantity', $quantityRequested);
                }
            }

            // 4. TODO: In the future, integrate Powertranz here.
            // For now, we record in-store reserve orders locally.

            // 5. Send Order Confirmation Email
            if ($order->customer_email) {
                try {
                    Mail::to($order->customer_email)->send(new OrderPlaced($order));
                } catch (\Throwable $e) {
                    // Log or ignore email failures in local/dev to avoid breaking the checkout
                    report($e);
                }
            }

            return $order;
        });
    }

    /**
     * Update an order's status and automatically handle stock restoration if cancelled.
     */
    public function updateStatus(Order $order, string $newStatus): void
    {
        if ($order->status === $newStatus) {
            return;
        }

        // If transitioning to 'cancelled' from an active status, restore stock
        if ($newStatus === 'cancelled' && $order->status !== 'cancelled') {
            $this->cancelOrder($order);

            return;
        }

        $order->update(['status' => $newStatus]);
    }

    /**
     * Cancel an order and restore the reserved inventory back to products/variants.
     */
    public function cancelOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Eager load items and relationships
            $order->loadMissing(['items.product', 'items.productVariant']);

            foreach ($order->items as $item) {
                if ($item->product_variant_id && $item->productVariant) {
                    // Restore variant inventory
                    $item->productVariant->increment('stock_quantity', $item->quantity);
                } elseif ($item->product_id && $item->product) {
                    // Restore base product inventory
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }

            $order->update(['status' => 'cancelled']);
        });
    }
}
