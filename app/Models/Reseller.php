<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Reseller extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'business_name', 'address', 'image',
        'nid_front', 'nid_back', 'guardian_nid_front', 'guardian_nid_back',
        'status', 'approved_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'image_url',
        'nid_front_url',
        'nid_back_url',
        'guardian_nid_front_url',
        'guardian_nid_back_url',
        'available_balance',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->image);
    }

    public function getNidFrontUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->nid_front);
    }

    public function getNidBackUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->nid_back);
    }

    public function getGuardianNidFrontUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->guardian_nid_front);
    }

    public function getGuardianNidBackUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->guardian_nid_back);
    }

    protected function resolveFileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('uploads/' . ltrim($path, '/'));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function orders()
    {
        return $this->hasMany(Sale::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(ResellerWithdrawal::class);
    }

    public function getTotalProfitAttribute(): float
    {
        return (float) $this->sales()->withoutGlobalScopes()->whereIn('order_status', ['completed', 'complete'])->sum('reseller_profit');
    }

    /**
     * Total delivery charge deducted for returned orders.
     * When a reseller's order is returned, shipping cost is charged against the reseller's balance.
     */
    public function getTotalReturnChargeAttribute(): float
    {
        return (float) $this->sales()->withoutGlobalScopes()
            ->whereIn('order_status', ['return', 'returned'])
            ->sum('delivery_charge');
    }

    /**
     * Number of returned orders for this reseller
     */
    public function getReturnedOrdersCountAttribute(): int
    {
        return $this->sales()->withoutGlobalScopes()
            ->whereIn('order_status', ['return', 'returned'])
            ->count();
    }

    /**
     * Net profit earned after deducting shipping charges of returned orders
     */
    public function getNetProfitAttribute(): float
    {
        return round($this->total_profit - $this->total_return_charge, 2);
    }

    public function getTotalWithdrawnAttribute(): float
    {
        return (float) $this->withdrawals()->where('status', 'approved')->sum('amount');
    }

    public function getPendingWithdrawalsAttribute(): float
    {
        return (float) $this->withdrawals()->where('status', 'pending')->sum('amount');
    }

    /**
     * Available wallet balance:
     * (Total Completed Profit - Total Return Delivery Charges - Total Approved Withdrawn).
     * If return charges exceed profit, this balance will be negative (e.g. -60.00).
     * Subsequent completed order profits will naturally add to/offset this negative amount.
     */
    public function getAvailableBalanceAttribute(): float
    {
        return round($this->total_profit - $this->total_return_charge - $this->total_withdrawn, 2);
    }

    /**
     * Actual withdrawable balance:
     * Cannot be negative, and cannot withdraw while in debt/negative balance.
     */
    public function getWithdrawableBalanceAttribute(): float
    {
        if ($this->available_balance <= 0) {
            return 0.0;
        }
        return max(0, round($this->available_balance - $this->pending_withdrawals, 2));
    }

    public function getTotalSalesAmountAttribute(): float
    {
        return (float) $this->sales()->withoutGlobalScopes()->sum('total');
    }

    public function getPendingProfitAttribute(): float
    {
        return (float) $this->sales()->withoutGlobalScopes()
            ->whereNotIn('order_status', ['completed', 'complete', 'cancelled', 'returned', 'return'])
            ->sum('reseller_profit');
    }
}
