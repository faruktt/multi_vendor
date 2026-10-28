<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttemptLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'email',
        'user_agent',
        'location',
        'is_success',
    ];

    protected $casts = [
        'is_success' => 'boolean',
    ];
}

