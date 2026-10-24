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
        'type',
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

    public function scopeMarketplace($query)
    {
        return $query->where('type', 'marketplace');
    }

    public function scopePurchase($query)
    {
        return $query->where('type', 'purchase');
    }

    public function isMarketplace(): bool
    {
        return $this->type === 'marketplace';
    }

    public function isPurchase(): bool
    {
        return $this->type === 'purchase';
    }

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

        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'uploads/')) {
            return asset($clean);
        }

        if (str_starts_with($clean, 'storage/')) {
            return asset($clean);
        }

        if (file_exists(public_path('uploads/' . $clean))) {
            return asset('uploads/' . $clean);
        }

        if (file_exists(public_path($clean))) {
            return asset($clean);
        }

        return asset('uploads/' . $clean);
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
        return (float) $this->saleItems()
            ->whereHas('sale', fn($q) => $q->withoutGlobalScopes()->where(function ($sq) {
                $sq->where('channel', 'pos')->orWhereIn('order_status', ['completed', 'complete']);
            }))
            ->sum('subtotal');
    }

    public function completedSales(): float
    {
        return (float) $this->saleItems()
            ->whereHas('sale', fn($q) => $q->withoutGlobalScopes()->whereIn('order_status', ['completed', 'complete']))
            ->sum('subtotal');
    }

    public function totalAdminCommission(): float
    {
        return (float) $this->saleItems()
            ->whereHas('sale', fn($q) => $q->withoutGlobalScopes()->whereIn('order_status', ['completed', 'complete']))
            ->sum('admin_commission_amount');
    }

    /**
     * Completed Net Earnings:
     * Only sales where order_status is 'completed' or 'complete'.
     * Profit is credited and withdrawable only when the order is complete.
     */
    public function totalNetEarnings(): float
    {
        return (float) $this->saleItems()
            ->whereHas('sale', fn($q) => $q->withoutGlobalScopes()->whereIn('order_status', ['completed', 'complete']))
            ->sum('supplier_earning');
    }

    /**
     * Pending Profit:
     * Earnings from in-progress/active orders that are placed but not yet completed.
     */
    public function pendingEarnings(): float
    {
        return (float) $this->saleItems()
            ->whereHas('sale', fn($q) => $q->withoutGlobalScopes()->whereNotIn('order_status', ['completed', 'complete', 'cancelled', 'returned', 'return']))
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

