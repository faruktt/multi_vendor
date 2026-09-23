<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerWithdrawal extends Model
{
    protected $fillable = [
        'reseller_id',
        'amount',
        'payment_method',
        'payment_details',
        'status',
        'note',
        'admin_note',
        'created_by',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
