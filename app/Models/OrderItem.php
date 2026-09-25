<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the specific product variant chosen (if any).
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Product name accessor fallback.
     */
    protected function productName(): Attribute
    {
        return Attribute::get(fn () => $this->product?->name ?? 'Product');
    }

    /**
     * Line item subtotal in cents.
     */
    protected function subtotal(): Attribute
    {
        return Attribute::get(fn () => $this->quantity * $this->unit_price);
    }

    /**
     * Formatted subtotal in dollars (e.g. "$35.00").
     */
    protected function formattedSubtotal(): Attribute
    {
        return Attribute::get(fn () => '$'.number_format($this->subtotal / 100, 2));
    }
}
