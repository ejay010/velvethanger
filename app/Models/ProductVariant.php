<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class ProductVariant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the product that owns this variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all images specific to this variant.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_variant_id');
    }

    /**
     * Get the featured image specific to this variant.
     */
    public function featuredImage(): HasOne
    {
        return $this->hasOne(ProductImage::class, 'product_variant_id')->where('is_featured', true);
    }

    /**
     * Get effective price in cents (variant price or fallback to product price).
     */
    protected function effectivePrice(): Attribute
    {
        return Attribute::get(function () {
            return ($this->price !== null && $this->price > 0)
                ? $this->price
                : $this->product->price;
        });
    }

    /**
     * Get effective list of images (variant specific images, or fallback to product images).
     */
    public function getEffectiveImagesAttribute()
    {
        if ($this->images->count() > 0) {
            return $this->images;
        }

        return $this->product->images;
    }

    /**
     * Get absolute URL for featured image (variant specific featured, or fallback to product featured URL).
     */
    protected function featuredImageUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->featuredImage) {
                return Storage::url($this->featuredImage->image_path);
            }

            $firstImage = $this->images->first();
            if ($firstImage) {
                return Storage::url($firstImage->image_path);
            }

            return $this->product->featured_image_url;
        });
    }
}
