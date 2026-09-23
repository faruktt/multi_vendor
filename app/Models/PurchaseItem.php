<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id', 'product_id', 'product_name',
        'quantity', 'unit_cost', 'subtotal',
    ];

    public function product()     { return $this->belongsTo(Product::class); }
    public function purchase()    { return $this->belongsTo(Purchase::class); }
    public function returnItems() { return $this->hasMany(PurchaseReturnItem::class); }
}
