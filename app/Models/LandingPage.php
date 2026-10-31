<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LandingPage extends Model
{
    protected $fillable = [
        'vendor_id',
        'title',
        'slug',
        'status',
        'page_theme',
        'hero_badge',
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'hero_video_url',
        'countdown_end',
        'countdown_hours',
        'features',
        'specifications',
        'gallery_images',
        'why_choose_us',
        'testimonials',
        'faq',
        'free_shipping',
        'delivery_charge_inside',
        'delivery_charge_sub_dhaka',
        'delivery_charge_outside',
        'cta_button_text',
        'order_form_title',
        'fb_pixel_id',
        'tiktok_pixel_id',
        'custom_css',
        'views_count',
        'orders_count',
        'created_by',
    ];

    protected $casts = [
        'features'                  => 'array',
        'gallery_images'            => 'array',
        'why_choose_us'             => 'array',
        'testimonials'              => 'array',
        'faq'                       => 'array',
        'countdown_end'             => 'datetime',
        'free_shipping'             => 'boolean',
        'delivery_charge_inside'    => 'decimal:2',
        'delivery_charge_sub_dhaka' => 'decimal:2',
        'delivery_charge_outside'   => 'decimal:2',
        'views_count'               => 'integer',
        'orders_count'              => 'integer',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function landingPageProducts()
    {
        return $this->hasMany(LandingPageProduct::class)->orderBy('sort_order');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'landing_page_products')
            ->withPivot([
                'id',
                'package_title',
                'package_subtitle',
                'custom_price',
                'custom_old_price',
                'is_default',
                'sort_order',
            ])
            ->withTimestamps()
            ->orderBy('landing_page_products.sort_order');
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        if (empty($this->hero_image)) {
            // fallback to first product image if available
            return $this->landingPageProducts->first()?->product?->image_url;
        }

        if (str_starts_with($this->hero_image, 'http://') || str_starts_with($this->hero_image, 'https://')) {
            return $this->hero_image;
        }

        return Storage::disk('uploads')->url($this->hero_image);
    }

    public function getGalleryImageUrlsAttribute(): array
    {
        $urls = [];
        $images = $this->gallery_images ?: [];

        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }

        foreach ($images as $img) {
            if (empty($img)) continue;
            if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                $urls[] = $img;
            } else {
                $urls[] = Storage::disk('uploads')->url($img);
            }
        }

        return $urls;
    }

    public function getPublicUrlAttribute(): string
    {
        return route('landing.show', $this->slug);
    }

    public function getDefaultPackageAttribute(): ?LandingPageProduct
    {
        return $this->landingPageProducts->firstWhere('is_default', true) ?? $this->landingPageProducts->first();
    }

    /** Theme configuration details */
    public function getThemeConfigAttribute(): array
    {
        $themes = [
            'emerald' => [
                'name'         => 'Emerald Luxury',
                'primary'      => '#059669',
                'primary_dark' => '#047857',
                'accent'       => '#10b981',
                'gradient'     => 'from-emerald-700 via-teal-700 to-emerald-900',
                'badge_bg'     => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'btn_bg'       => 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700',
                'ring'         => 'focus:ring-emerald-500',
                'glow'         => 'shadow-emerald-500/25',
                'text_accent'  => 'text-emerald-600',
            ],
            'blue' => [
                'name'         => 'Royal Sapphire',
                'primary'      => '#1d4ed8',
                'primary_dark' => '#1e40af',
                'accent'       => '#3b82f6',
                'gradient'     => 'from-blue-700 via-indigo-800 to-slate-900',
                'badge_bg'     => 'bg-blue-50 text-blue-800 border-blue-200',
                'btn_bg'       => 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700',
                'ring'         => 'focus:ring-blue-500',
                'glow'         => 'shadow-blue-500/25',
                'text_accent'  => 'text-blue-600',
            ],
            'dark' => [
                'name'         => 'Midnight Carbon',
                'primary'      => '#0f172a',
                'primary_dark' => '#020617',
                'accent'       => '#f59e0b',
                'gradient'     => 'from-slate-950 via-slate-900 to-black',
                'badge_bg'     => 'bg-amber-400/10 text-amber-300 border-amber-400/30',
                'btn_bg'       => 'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600',
                'ring'         => 'focus:ring-amber-500',
                'glow'         => 'shadow-amber-500/25',
                'text_accent'  => 'text-amber-500',
            ],
            'purple' => [
                'name'         => 'Imperial Violet',
                'primary'      => '#7e22ce',
                'primary_dark' => '#6b21a8',
                'accent'       => '#a855f7',
                'gradient'     => 'from-purple-800 via-violet-900 to-slate-950',
                'badge_bg'     => 'bg-purple-50 text-purple-800 border-purple-200',
                'btn_bg'       => 'bg-gradient-to-r from-purple-600 to-violet-600 hover:from-purple-700 hover:to-violet-700',
                'ring'         => 'focus:ring-purple-500',
                'glow'         => 'shadow-purple-500/25',
                'text_accent'  => 'text-purple-600',
            ],
            'rose' => [
                'name'         => 'Ruby Crimson',
                'primary'      => '#e11d48',
                'primary_dark' => '#be123c',
                'accent'       => '#f43f5e',
                'gradient'     => 'from-rose-700 via-pink-800 to-slate-900',
                'badge_bg'     => 'bg-rose-50 text-rose-800 border-rose-200',
                'btn_bg'       => 'bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700',
                'ring'         => 'focus:ring-rose-500',
                'glow'         => 'shadow-rose-500/25',
                'text_accent'  => 'text-rose-600',
            ],
            'amber' => [
                'name'         => 'Golden Premium',
                'primary'      => '#d97706',
                'primary_dark' => '#b45309',
                'accent'       => '#f59e0b',
                'gradient'     => 'from-amber-700 via-orange-800 to-stone-900',
                'badge_bg'     => 'bg-amber-50 text-amber-800 border-amber-200',
                'btn_bg'       => 'bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700',
                'ring'         => 'focus:ring-amber-500',
                'glow'         => 'shadow-amber-500/25',
                'text_accent'  => 'text-amber-600',
            ],
        ];

        return $themes[$this->page_theme] ?? $themes['emerald'];
    }
}

