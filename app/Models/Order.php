<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The items associated with this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * The customer user account (if not a guest).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor alias so $order->total_price maps to total_amount.
     */
    protected function totalPrice(): Attribute
    {
        return Attribute::get(fn () => $this->total_amount);
    }

    /**
     * Formatted total amount in dollars (e.g. "$45.00").
     */
    protected function formattedTotal(): Attribute
    {
        return Attribute::get(fn () => '$'.number_format($this->total_amount / 100, 2));
    }
}
