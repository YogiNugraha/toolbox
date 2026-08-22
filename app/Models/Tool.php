<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'category',
        'icon',
        'image',
        'component',
        'badge',
        'is_highlighted',
        'is_active',
        'is_maintenance',
        'maintenance_message',
        'sort_order',
        'total_usage_count',
    ];

    /**
     * Get image URL or asset illustration fallback.
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            if (str_starts_with($this->image, 'http') || str_starts_with($this->image, '/')) {
                return $this->image;
            }
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->image)) {
                return \Illuminate\Support\Facades\Storage::url($this->image);
            }
            return asset($this->image);
        }

        return null;
    }

    protected $casts = [
        'is_highlighted' => 'boolean',
        'is_active' => 'boolean',
        'is_maintenance' => 'boolean',
        'sort_order' => 'integer',
        'total_usage_count' => 'integer',
    ];

    protected static $activeToolsCache = null;
    protected static $allToolsCache = null;

    protected static function booted()
    {
        static::saved(function () {
            static::$activeToolsCache = null;
            static::$allToolsCache = null;
        });

        static::deleted(function () {
            static::$activeToolsCache = null;
            static::$allToolsCache = null;
        });
    }

    /**
     * Get all active tools, cached in-memory per request.
     */
    public static function getActiveTools()
    {
        if (static::$activeToolsCache === null) {
            if (static::count() === 0) {
                static::syncFromConfig();
            }

            static::$activeToolsCache = static::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        return static::$activeToolsCache;
    }

    /**
     * Get all tools for admin management.
     */
    public static function getAllTools()
    {
        if (static::$allToolsCache === null) {
            if (static::count() === 0) {
                static::syncFromConfig();
            }

            static::$allToolsCache = static::orderBy('sort_order')->orderBy('name')->get();
        }

        return static::$allToolsCache;
    }

    /**
     * Get highlighted tools for landing page showcase.
     */
    public static function getHighlightedTools()
    {
        return static::getActiveTools()->where('is_highlighted', true);
    }

    /**
     * Get all active categories with tool counts.
     */
    public static function getCategories()
    {
        return static::getActiveTools()
            ->groupBy('category')
            ->map(function ($tools, $category) {
                return [
                    'name' => $category,
                    'count' => $tools->count(),
                    'slug' => \Illuminate\Support\Str::slug($category),
                ];
            })
            ->values();
    }

    /**
     * Increment total usage counter for a tool.
     */
    public static function incrementUsage(string $slug): void
    {
        static::where('slug', $slug)->increment('total_usage_count');
    }

    /**
     * Sync initial tools from config/tools.php
     */
    public static function syncFromConfig(): void
    {
        $configTools = config('tools', []);
        $order = 0;

        foreach ($configTools as $t) {
            $order += 10;
            static::updateOrCreate(
                ['slug' => $t['slug']],
                [
                    'name' => $t['name'] ?? ucfirst($t['slug']),
                    'description' => $t['description'] ?? '',
                    'category' => $t['category'] ?? 'General',
                    'icon' => $t['icon'] ?? 'wrench',
                    'component' => $t['component'] ?? null,
                    'is_active' => true,
                    'is_maintenance' => false,
                    'sort_order' => $order,
                ]
            );
        }
    }
}
