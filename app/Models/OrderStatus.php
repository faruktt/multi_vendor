<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $fillable = ['key', 'label', 'color', 'sort_order'];

    protected $casts = [
        'is_protected' => 'boolean',
    ];
}
