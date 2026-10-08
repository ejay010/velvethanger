<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the products belonging to this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Accessor to get the absolute public URL of the category featured image.
     */
    protected function featuredImageUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->image_url) {
                if (str_starts_with($this->image_url, 'http')) {
                    return $this->image_url;
                }

                if (str_starts_with($this->image_url, '/storage/')) {
                    return $this->image_url;
                }

                return Storage::url($this->image_url);
            }

            // Fallback: check first product with an image
            $firstProductWithImage = $this->products()->whereNotNull('image_url')->orWhereHas('images')->first();
            if ($firstProductWithImage?->featured_image_url) {
                return $firstProductWithImage->featured_image_url;
            }

            // Default luxury aesthetic fallback by slug
            return match ($this->slug) {
                'dresses' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?q=80&w=600&auto=format&fit=crop',
                'tops' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?q=80&w=600&auto=format&fit=crop',
                'bottoms' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?q=80&w=600&auto=format&fit=crop',
                'accessories' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?q=80&w=600&auto=format&fit=crop',
                'shoes' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?q=80&w=600&auto=format&fit=crop',
                default => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=600&auto=format&fit=crop',
            };
        });
    }
}
