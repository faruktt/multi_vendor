<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierSetting extends Model
{
    protected $table = 'courier_settings';

    protected $fillable = [
        'code',
        'name',
        'is_active',
        'api_key',
        'secret_key',
        'client_id',
        'store_id',
        'username',
        'password',
        'base_url',
        'delivery_type',
        'additional_settings',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'additional_settings' => 'array',
    ];

    /**
     * Scope for active couriers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Find by courier code
     */
    public static function byCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    /**
     * Get all active couriers
     */
    public static function getActiveCouriers()
    {
        return static::active()->get();
    }

    /**
     * Alias for client_secret mapped to secret_key
     */
    public function getClientSecretAttribute(): ?string
    {
        return $this->secret_key;
    }

    /**
     * Get logo or icon identifier
     */
    public function getIconAttribute(): string
    {
        return match ($this->code) {
            'steadfast' => 'fas fa-shipping-fast',
            'pathao'    => 'fas fa-motorcycle',
            'redx'      => 'fas fa-truck-fast',
            default     => 'fas fa-truck',
        };
    }

    /**
     * Get theme color
     */
    public function getThemeColorAttribute(): string
    {
        return match ($this->code) {
            'steadfast' => '#0d9488', // teal
            'pathao'    => '#dc2626', // red
            'redx'      => '#e11d48', // rose / crimson
            default     => '#2563eb', // blue
        };
    }
}
