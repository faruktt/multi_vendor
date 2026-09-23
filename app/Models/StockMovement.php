<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vendor;

class StockMovement extends Model
{
    use BelongsToVendor;

    protected $fillable = ['vendor_id', 'product_id', 'type', 'quantity', 'reference_type', 'reference_id', 'note'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}

