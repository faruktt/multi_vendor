<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleNote extends Model
{
    protected $fillable = [
        'sale_id',
        'user_id',
        'note',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
