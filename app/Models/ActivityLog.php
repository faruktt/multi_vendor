<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'vendor_id',
        'action',
        'module',
        'subject_type',
        'subject_id',
        'subject_title',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getActionBadgeClassAttribute(): string
    {
        return match ($this->action) {
            'created' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'updated' => 'bg-amber-50 text-amber-700 border-amber-200',
            'deleted' => 'bg-rose-50 text-rose-700 border-rose-200',
            'login'   => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            default   => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            'created' => 'fas fa-plus text-emerald-600',
            'updated' => 'fas fa-pen text-amber-600',
            'deleted' => 'fas fa-trash text-rose-600',
            'login'   => 'fas fa-arrow-right-to-bracket text-indigo-600',
            default   => 'fas fa-circle-info text-slate-500',
        };
    }

    public function getModuleIconAttribute(): string
    {
        return match ($this->module) {
            'Product', 'ProductVariant' => 'fas fa-box text-blue-500',
            'Category'                  => 'fas fa-tags text-indigo-500',
            'Sale', 'Order'             => 'fas fa-receipt text-emerald-500',
            'Customer'                  => 'fas fa-users text-purple-500',
            'Supplier'                  => 'fas fa-industry text-amber-500',
            'Purchase'                  => 'fas fa-truck text-sky-500',
            'User', 'Staff'             => 'fas fa-user-gear text-slate-600',
            'Coupon'                    => 'fas fa-ticket text-rose-500',
            'Banner'                    => 'fas fa-image text-pink-500',
            'IncomeExpense'             => 'fas fa-coins text-amber-600',
            'CourierSetting'            => 'fas fa-truck-fast text-teal-600',
            'OrderStatus'               => 'fas fa-list-check text-blue-600',
            default                     => 'fas fa-cube text-slate-400',
        };
    }
}
