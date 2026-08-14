<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the category that this product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all images for this product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get all variants for this product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get active variants for this product.
     */
    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    /**
     * Check if this product has any variants.
     */
    protected function hasVariants(): Attribute
    {
        return Attribute::get(fn () => $this->variants->count() > 0);
    }

    /**
     * Get total available stock across variants or base product stock.
     */
    protected function totalStock(): Attribute
    {
        return Attribute::get(function () {
            if ($this->has_variants) {
                return $this->variants->where('is_active', true)->sum('stock_quantity');
            }

            return $this->stock_quantity;
        });
    }

    /**
     * Get the featured image for this product.
     */
    public function featuredImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_featured', true);
    }

    /**
     * Accessor to get the absolute public URL of the featured image (or fallback).
     */
    protected function featuredImageUrl(): Attribute
    {
        return Attribute::get(function () {
            // 1. Check if there's a featured ProductImage relationship
            if ($this->featuredImage) {
                return Storage::url($this->featuredImage->image_path);
            }

            // 2. Check if there's a legacy image_url column value
            if ($this->image_url) {
                return str_starts_with($this->image_url, 'http')
                    ? $this->image_url
                    : Storage::url($this->image_url);
            }

            // 3. Check if any image exists in images relation
            $firstImage = $this->images->first();
            if ($firstImage) {
                return Storage::url($firstImage->image_path);
            }

            return null;
        });
    }
}
