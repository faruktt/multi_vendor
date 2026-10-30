<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    protected $fillable = [
        'sale_return_id',
        'sale_item_id',
        'product_id',
        'supplier_id',
        'product_variant_id',
        'variant_name',
        'quantity',
        'unit_price',
        'subtotal',
        'status',
        'returned_at',
        'returned_by',
        'notes',
    ];

    protected $casts = [
        'unit_price'  => 'decimal:2',
        'subtotal'    => 'decimal:2',
        'returned_at' => 'datetime',
    ];

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }
}
