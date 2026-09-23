<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, BelongsToVendor;

    protected $fillable = [
        'vendor_id',
        'name',
        'phone',
        'email',
        'password',
        'address',
        'district',
        'thana',
        'image',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password'          => 'hashed',
        'email_verified_at' => 'datetime',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Digits-only, country-code-prefixed number for wa.me / tel: links — assumes BD numbers. */
    public function getWhatsappNumberAttribute(): ?string
    {
        if (!$this->phone) return null;

        $digits = preg_replace('/\D+/', '', $this->phone);
        if (!$digits) return null;

        if (str_starts_with($digits, '880')) return $digits;
        if (str_starts_with($digits, '0')) return '880' . substr($digits, 1);

        return $digits;
    }
}
