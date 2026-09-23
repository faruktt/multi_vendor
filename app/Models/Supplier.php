<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Supplier extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'vendor_id',
        'name',
        'company_name',
        'phone',
        'email',
        'password',
        'address',
        'status',
        'commission_percentage',
        'approved_at',
        'approved_by',
        'logo',
        'bank_info',
        'bkash_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'approved_at'           => 'datetime',
        'commission_percentage' => 'decimal:2',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ?: $this->name;
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) return null;
        if (str_contains($this->logo, 'http://') || str_contains($this->logo, 'https://')) {
            return $this->logo;
        }
        return asset('uploads/' . $this->logo);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(SupplierWithdrawal::class)->latest();
    }

    public function totalSales(): float
    {
        return (float) $this->saleItems()->sum('subtotal');
    }

    public function totalAdminCommission(): float
    {
        return (float) $this->saleItems()->sum('admin_commission_amount');
    }

    public function totalNetEarnings(): float
    {
        return (float) $this->saleItems()
            ->whereHas('sale', fn($q) => $q->whereNotIn('order_status', ['cancelled', 'returned']))
            ->sum('supplier_earning');
    }

    public function totalWithdrawnAmount(): float
    {
        return (float) $this->withdrawals()->where('status', 'approved')->sum('amount');
    }

    public function pendingWithdrawnAmount(): float
    {
        return (float) $this->withdrawals()->where('status', 'pending')->sum('amount');
    }

    public function availableBalance(): float
    {
        $balance = $this->totalNetEarnings() - $this->totalWithdrawnAmount();
        return max(0.00, round($balance, 2));
    }

    public function withdrawableBalance(): float
    {
        $balance = $this->availableBalance() - $this->pendingWithdrawnAmount();
        return max(0.00, round($balance, 2));
    }
}

