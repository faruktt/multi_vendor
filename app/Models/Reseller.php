<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Reseller extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'business_name', 'address', 'image', 'status', 'approved_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('uploads/' . ltrim($this->image, '/'));
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

    public function getTotalWithdrawnAttribute(): float
    {
        return (float) $this->withdrawals()->where('status', 'approved')->sum('amount');
    }

    public function getPendingWithdrawalsAttribute(): float
    {
        return (float) $this->withdrawals()->where('status', 'pending')->sum('amount');
    }

    public function getAvailableBalanceAttribute(): float
    {
        return max(0, $this->total_profit - $this->total_withdrawn);
    }

    public function getWithdrawableBalanceAttribute(): float
    {
        return max(0, $this->available_balance - $this->pending_withdrawals);
    }

    public function getTotalSalesAmountAttribute(): float
    {
        return (float) $this->sales()->withoutGlobalScopes()->sum('total');
    }

    public function getPendingProfitAttribute(): float
    {
        return (float) $this->sales()->withoutGlobalScopes()
            ->whereNotIn('order_status', ['completed', 'complete', 'cancelled', 'returned'])
            ->sum('reseller_profit');
    }
}
