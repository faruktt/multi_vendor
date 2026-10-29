<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'reason',
        'location',
        'user_agent',
        'attempted_email',
        'failed_attempts',
        'is_active',
        'blocked_by',
        'blocked_at',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'blocked_at'  => 'datetime',
        'failed_attempts' => 'integer',
    ];

    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

