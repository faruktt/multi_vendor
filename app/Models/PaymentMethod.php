<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['key', 'label', 'details', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_protected' => 'boolean',
    ];

    /** [key => label] for active methods, in display order — the shared source for every payment dropdown. */
    public static function options(): array
    {
        return static::where('is_active', true)->orderBy('sort_order')->pluck('label', 'key')->all();
    }
}
