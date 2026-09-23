<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'supplier_id',
        'admin_commission_rate',
        'admin_commission_amount',
        'supplier_earning',
        'product_variant_id',
        'variant_name',
        'quantity',
        'unit_price',
        'subtotal',
        'reseller_buy_price',
        'reseller_profit',
    ];

    protected $casts = [
        'unit_price'              => 'decimal:2',
        'subtotal'                => 'decimal:2',
        'admin_commission_rate'   => 'decimal:2',
        'admin_commission_amount' => 'decimal:2',
        'supplier_earning'        => 'decimal:2',
        'reseller_buy_price'      => 'decimal:2',
        'reseller_profit'         => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (SaleItem $item) {
            if ($item->supplier_id && ($item->admin_commission_rate === null || $item->admin_commission_rate == 0)) {
                $product  = $item->product ?: Product::find($item->product_id);
                $supplier = $item->supplier ?: Supplier::find($item->supplier_id);
                $rate     = (float) ($product?->admin_commission_rate ?? $supplier?->commission_percentage ?? 5.00);
                $subtotal = (float) ($item->subtotal ?? ($item->quantity * $item->unit_price));
                $commission = round($subtotal * ($rate / 100), 2);
                
                $item->admin_commission_rate   = $rate;
                $item->admin_commission_amount = $commission;
                $item->supplier_earning        = round($subtotal - $commission, 2);
            }
        });
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function returnItems()
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}

