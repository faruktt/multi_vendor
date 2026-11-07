<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubCategory extends Model
{
    protected $table = 'sub_categories';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'image',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('uploads')->url($this->image) : null;
    }

    public function getParentNameAttribute(): ?string
    {
        return $this->category?->name;
    }

    public function getParentIdAttribute(): ?int
    {
        return $this->category_id;
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    protected static function booted()
    {
        static::creating(function (SubCategory $subCategory) {
            if (!$subCategory->slug) {
                $subCategory->slug = static::uniqueSlug($subCategory->name);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name) ?: 'subcategory';
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

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'sub_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('status')->orWhere('status', 'active');
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
