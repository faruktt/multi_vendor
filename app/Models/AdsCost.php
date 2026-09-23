<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdsCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'campaign_name',
        'ad_account',
        'cost_date',
        'amount',
        'currency',
        'amount_usd',
        'impressions',
        'clicks',
        'conversions',
        'target_url',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'cost_date'   => 'date:Y-m-d',
        'amount'      => 'decimal:2',
        'amount_usd'  => 'decimal:2',
        'impressions' => 'integer',
        'clicks'      => 'integer',
        'conversions' => 'integer',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
