<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use BelongsToVendor;

    protected $fillable = [
        'vendor_id', 'supplier_id', 'created_by', 'invoice_no',
        'subtotal', 'discount', 'total', 'paid_amount', 'due_amount',
        'payment_status', 'payment_method', 'note',
    ];

    public function supplier()  { return $this->belongsTo(Supplier::class); }
    public function items()     { return $this->hasMany(PurchaseItem::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function returns()   { return $this->hasMany(PurchaseReturn::class); }
}
