<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class IncomeExpense extends Model
{
    use BelongsToVendor;

    protected $fillable = ['vendor_id', 'category_id', 'created_by', 'type', 'amount', 'date', 'note', 'reference'];

    protected $casts = ['date' => 'date:Y-m-d', 'amount' => 'decimal:2'];

    public function category()  { return $this->belongsTo(IncomeExpenseCategory::class, 'category_id'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}
