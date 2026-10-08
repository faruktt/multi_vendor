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
        'image',
        'nid_front',
        'nid_back',
        'guardian_nid_front',
        'guardian_nid_back',
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

    protected $appends = [
        'display_name',
        'available_balance',
        'image_url',
        'nid_front_url',
        'nid_back_url',
        'guardian_nid_front_url',
        'guardian_nid_back_url',
    ];

    public function getAvailableBalanceAttribute(): float
    {
        return $this->availableBalance();
    }

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

    public function getImageUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->image ?: $this->logo);
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

    public function getLogoUrlAttribute(): ?string
    {
        return $this->getImageUrlAttribute();
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

