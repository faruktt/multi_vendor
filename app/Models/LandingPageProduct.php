<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageProduct extends Model
{
    protected $fillable = [
        'landing_page_id',
        'product_id',
        'package_title',
        'package_subtitle',
        'custom_price',
        'custom_old_price',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'custom_price'     => 'decimal:2',
        'custom_old_price' => 'decimal:2',
        'is_default'       => 'boolean',
        'sort_order'       => 'integer',
    ];

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** Effective display price (custom promotional price or product sale price) */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->custom_price !== null && $this->custom_price > 0) {
            return (float) $this->custom_price;
        }

        return (float) ($this->product?->sale_price ?? $this->product?->price ?? 0);
    }

    /** Effective old / struck-through price */
    public function getEffectiveOldPriceAttribute(): ?float
    {
        if ($this->custom_old_price !== null && $this->custom_old_price > 0) {
            return (float) $this->custom_old_price;
        }

        return $this->product?->old_price ? (float) $this->product->old_price : null;
    }
}

