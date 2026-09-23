<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    protected $fillable = [
        'product_id',
        'target_audience',
        'flash_price',
        'discount_percentage',
        'start_time',
        'end_time',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'flash_price'         => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'start_time'          => 'datetime',
        'end_time'            => 'datetime',
        'is_active'           => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope only currently active flash sales (within scheduled start/end times if set).
     */
    public function scopeActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_time')->orWhere('start_time', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_time')->orWhere('end_time', '>=', $now);
            });
    }

    /**
     * Scope flash sales for customer storefront.
     */
    public function scopeForCustomer($query)
    {
        return $query->where('target_audience', 'customer');
    }

    /**
     * Scope flash sales for reseller portal.
     */
    public function scopeForReseller($query)
    {
        return $query->where('target_audience', 'reseller');
    }

    /**
     * Scope active deals for specific audience.
     */
    public function scopeActiveFor($query, string $audience = 'customer')
    {
        return $query->active()->where('target_audience', $audience);
    }

    public function isForReseller(): bool
    {
        return $this->target_audience === 'reseller';
    }

    public function isForCustomer(): bool
    {
        return $this->target_audience === 'customer';
    }

    /**
     * Check if this flash deal is live right now.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->start_time && $now->lt($this->start_time)) {
            return false;
        }
        if ($this->end_time && $now->gt($this->end_time)) {
            return false;
        }

        return true;
    }

    /**
     * Get the discount savings amount in currency.
     */
    public function getDiscountAmountAttribute(): float
    {
        if ($this->target_audience === 'reseller') {
            $base = (float) ($this->product?->reseller_price ?? ($this->product?->price ?? 0));
        } else {
            $base = (float) ($this->product?->price ?? 0);
        }
        return max(0, $base - (float) $this->flash_price);
    }

    /**
     * Number of seconds remaining until end_time (or null if indefinite).
     */
    public function getTimeRemainingSecondsAttribute(): ?int
    {
        if (!$this->end_time) {
            return null;
        }
        return max(0, now()->diffInSeconds($this->end_time, false));
    }
}
