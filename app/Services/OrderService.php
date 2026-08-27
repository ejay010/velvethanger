<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlaced;

class OrderService
{
    /**
     * Process checkout from the given cart items.
     */
    public function processCheckout(?int $userId, array $cartItems, int $totalAmount, array $customerDetails = []): Order
    {
        // We use a database transaction so that if anything fails, we don't save a partial order
        return DB::transaction(function () use ($userId, $cartItems, $totalAmount, $customerDetails) {

            // 1. Create the Order record
            $order = Order::create([
                'user_id' => $userId,
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'customer_name' => $customerDetails['name'] ?? null,
                'customer_email' => $customerDetails['email'] ?? null,
                'customer_address' => $customerDetails['address'] ?? null,
            ]);

            // 2. Attach items to the order
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                ]);
            }

            // 3. TODO: In the future, integrate Powertranz here.
            // For now, we just record the order locally.
            // Example future integration:
            // $paymentResult = Powertranz::charge($totalAmount, $paymentMethod);
            // $order->update(['payment_reference' => $paymentResult->transaction_id, 'status' => 'paid']);

            // 4. Send Order Confirmation Email
            if ($order->customer_email) {
                Mail::to($order->customer_email)->send(new OrderPlaced($order));
            }

            return $order;
        });
    }
}
