<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'status',
        'sort_order',
        'show_on_homepage',
        'homepage_sort_order',
    ];

    protected $casts = [
        'show_on_homepage'    => 'boolean',
        'sort_order'          => 'integer',
        'homepage_sort_order' => 'integer',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('uploads')->url($this->image) : null;
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    protected static function booted()
    {
        static::creating(function (Category $category) {
            if (!$category->slug) {
                $category->slug = static::uniqueSlug($category->name);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $i = 2;

        while (
            static::where('slug', $slug)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'category_id');
    }

    /** Backward compatibility alias for storefront views expecting ->children */
    public function children()
    {
        return $this->hasMany(SubCategory::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('status')->orWhere('status', 'active');
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('homepage_sort_order')->orderBy('name');
    }

    public function scopeParents($query)
    {
        return $query;
    }
}
