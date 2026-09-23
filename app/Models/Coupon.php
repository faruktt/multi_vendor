<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'discount_type',
        'discount_amount',
        'min_order_amount',
        'max_discount_amount',
        'applicable_for',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'discount_amount'     => 'decimal:2',
        'min_order_amount'    => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'start_date'          => 'date',
        'end_date'            => 'date',
        'used_count'          => 'integer',
        'usage_limit'         => 'integer',
        'usage_limit_per_user'=> 'integer',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate discount amount given an order subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = ($subtotal * (float) $this->discount_amount) / 100;
            if ($this->max_discount_amount && $this->max_discount_amount > 0) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
            return round(min($discount, $subtotal), 2);
        }

        // Fixed amount discount
        return round(min((float) $this->discount_amount, $subtotal), 2);
    }

    /**
     * Validate whether coupon is eligible for given subtotal and target context.
     *
     * @param float $subtotal
     * @param string $target 'customer' or 'reseller'
     * @param string|null $userPhone
     * @param int|null $resellerId
     * @return array ['valid' => bool, 'discount' => float, 'message' => string]
     */
    public function validateFor(float $subtotal, string $target = 'customer', ?string $userPhone = null, ?int $resellerId = null): array
    {
        // 1. Check status
        if ($this->status !== 'active') {
            return ['valid' => false, 'discount' => 0, 'message' => 'This coupon is currently inactive.'];
        }

        // 2. Check date range
        $today = Carbon::today();
        if ($this->start_date && $today->lt($this->start_date)) {
            return ['valid' => false, 'discount' => 0, 'message' => 'This coupon is not active yet. Starts on ' . $this->start_date->format('d M Y') . '.'];
        }

        if ($this->end_date && $today->gt($this->end_date)) {
            return ['valid' => false, 'discount' => 0, 'message' => 'This coupon has expired on ' . $this->end_date->format('d M Y') . '.'];
        }

        // 3. Check overall usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'discount' => 0, 'message' => 'This coupon has reached its maximum total usage limit.'];
        }

        // 4. Check audience applicability
        if ($this->applicable_for !== 'everyone') {
            if ($this->applicable_for === 'customer' && $target !== 'customer') {
                return ['valid' => false, 'discount' => 0, 'message' => 'This coupon is only valid for customer retail orders.'];
            }
            if ($this->applicable_for === 'reseller' && $target !== 'reseller') {
                return ['valid' => false, 'discount' => 0, 'message' => 'This coupon is only valid for reseller orders.'];
            }
        }

        // 5. Check minimum order amount condition
        if ($this->min_order_amount && $subtotal < (float) $this->min_order_amount) {
            $formattedMin = number_format((float) $this->min_order_amount, 0);
            return ['valid' => false, 'discount' => 0, 'message' => "Minimum order amount of ৳{$formattedMin} is required to use this coupon."];
        }

        // 6. Check per-user usage limit if phone or resellerId provided
        if ($this->usage_limit_per_user && $this->usage_limit_per_user > 0) {
            $userUsageQuery = $this->usages();
            if ($target === 'customer' && $userPhone) {
                $userUsageCount = $userUsageQuery->where('customer_phone', $userPhone)->count();
                if ($userUsageCount >= $this->usage_limit_per_user) {
                    return ['valid' => false, 'discount' => 0, 'message' => 'You have already reached the maximum usage limit for this coupon.'];
                }
            } elseif ($target === 'reseller' && $resellerId) {
                $userUsageCount = $userUsageQuery->where('reseller_id', $resellerId)->count();
                if ($userUsageCount >= $this->usage_limit_per_user) {
                    return ['valid' => false, 'discount' => 0, 'message' => 'You have already reached the maximum usage limit for this coupon.'];
                }
            }
        }

        $discount = $this->calculateDiscount($subtotal);

        return [
            'valid'    => true,
            'discount' => $discount,
            'message'  => 'Coupon applied successfully!',
            'coupon'   => $this,
        ];
    }
}
