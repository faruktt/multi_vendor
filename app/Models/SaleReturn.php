<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use BelongsToVendor;

    protected $fillable = ['vendor_id', 'sale_id', 'reason', 'refund_amount', 'refund_method', 'created_by'];

    protected $casts = [
        'refund_amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
