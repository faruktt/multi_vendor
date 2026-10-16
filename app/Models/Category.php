<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use BelongsToVendor;

    protected $fillable = ['vendor_id', 'parent_id', 'name', 'slug', 'image', 'status', 'show_on_homepage', 'homepage_sort_order'];

    protected $casts = [
        'show_on_homepage' => 'boolean',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return Storage::disk('uploads')->url($this->image);
        }

        // Fallback: check if sibling category row with same name has an uploaded image
        $siblingImage = static::withoutGlobalScopes()
            ->where('name', $this->name)
            ->whereNotNull('image')
            ->value('image');

        return $siblingImage ? Storage::disk('uploads')->url($siblingImage) : null;
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    protected static function booted()
    {
        static::creating(function (Category $category) {
            if (!$category->slug) {
                $category->slug = static::uniqueSlug($category->vendor_id, $category->name);
            }
        });
    }

    public static function uniqueSlug(int $vendorId, string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $i = 2;

        while (
            static::withoutGlobalScopes()
                ->where('vendor_id', $vendorId)
                ->where('slug', $slug)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function subCategories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSubcategories($query)
    {
        return $query->whereNotNull('parent_id');
    }
}
