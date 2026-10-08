<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Cache key for site settings.
     */
    const CACHE_KEY = 'velvet_site_settings';

    /**
     * Retrieve a setting value by key with optional fallback default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::allCached();

        return $settings[$key] ?? $default;
    }

    /**
     * Store or update a setting value and invalidate the cache.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): static
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget(static::CACHE_KEY);

        return $setting;
    }

    /**
     * Get all settings cached as a key => value array.
     */
    public static function allCached(): array
    {
        return Cache::rememberForever(static::CACHE_KEY, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear the settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }
}
