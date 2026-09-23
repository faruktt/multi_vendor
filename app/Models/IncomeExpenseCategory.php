<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class IncomeExpenseCategory extends Model
{
    use BelongsToVendor;

    protected $fillable = ['vendor_id', 'name', 'type', 'color'];

    public function transactions()
    {
        return $this->hasMany(IncomeExpense::class, 'category_id');
    }
}
