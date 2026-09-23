<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'variant_name', 'attributes',
        'color_id', 'size_id', 'color_label', 'size_label',
        'price', 'cost_price', 'stock_qty', 'sku', 'barcode',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price'      => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    /** Auto-generate variant_name from color + size labels before saving. */
    protected static function booted(): void
    {
        static::saving(function (ProductVariant $v) {
            $parts = array_filter([$v->color_label, $v->size_label]);
            if ($parts) {
                $v->variant_name = implode(' / ', $parts);
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }
}
