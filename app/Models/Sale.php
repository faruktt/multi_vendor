<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use BelongsToVendor;

    protected $fillable = [
        'vendor_id', 'customer_id', 'reseller_id', 'supplier_id', 'coupon_id', 'coupon_code',
        'invoice_no', 'subtotal', 'discount', 'tax', 'total', 'paid_amount', 'due_amount',
        'payment_status', 'payment_method', 'order_status', 'channel',
        'district', 'thana', 'delivery_zone', 'delivery_charge',
        'courier_name', 'courier_tracking_code', 'courier_consignment_id', 'courier_status', 'courier_response', 'courier_sent_at',
        'created_by', 'note', 'reseller_profit'
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount'        => 'decimal:2',
        'tax'             => 'decimal:2',
        'total'           => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'due_amount'      => 'decimal:2',
        'reseller_profit' => 'decimal:2',
        'courier_sent_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($sale) {
            if (empty($sale->invoice_no)) {
                $prefix = ($sale->channel === 'reseller' || !empty($sale->reseller_id)) ? 'RES' : 'INV';
                $sale->invoice_no = self::generateInvoiceNo($prefix);
            }
        });
    }

    /**
     * Generate sequential invoice number (e.g. RES-000001 or INV-000001)
     */
    public static function generateInvoiceNo(string $prefix = 'INV'): string
    {
        $prefix = strtoupper(trim($prefix));
        $prefixLen = strlen($prefix) + 2;

        try {
            $lastInvoice = self::withoutGlobalScopes()
                ->where('invoice_no', 'REGEXP', "^{$prefix}-[0-9]+$")
                ->orderByRaw("CAST(SUBSTRING(invoice_no, {$prefixLen}) AS UNSIGNED) DESC")
                ->value('invoice_no');
        } catch (\Throwable $e) {
            $lastInvoice = null;
            $invoices = self::withoutGlobalScopes()
                ->where('invoice_no', 'like', "{$prefix}-%")
                ->pluck('invoice_no');
            $maxNum = 0;
            foreach ($invoices as $inv) {
                if (preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', $inv, $m)) {
                    $num = (int) $m[1];
                    if ($num > $maxNum) {
                        $maxNum = $num;
                    }
                }
            }
            if ($maxNum > 0) {
                $lastInvoice = sprintf('%s-%06d', $prefix, $maxNum);
            }
        }

        $nextNum = 1;
        if ($lastInvoice && preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', $lastInvoice, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
        }

        do {
            $candidate = sprintf('%s-%06d', $prefix, $nextNum);
            $exists = self::withoutGlobalScopes()->where('invoice_no', $candidate)->exists();
            if (!$exists) {
                return $candidate;
            }
            $nextNum++;
        } while (true);
    }

    /**
     * Check if sale order has been sent to a courier
     */
    public function isSentToCourier(): bool
    {
        return !empty($this->courier_tracking_code) || !empty($this->courier_consignment_id);
    }

    /**
     * Get human-readable courier display name
     */
    public function getCourierDisplayNameAttribute(): string
    {
        return match (strtolower((string)$this->courier_name)) {
            'steadfast' => 'Steadfast',
            'pathao'    => 'Pathao',
            'redx'      => 'RedX',
            default     => ucfirst((string)$this->courier_name),
        };
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function notes()
    {
        return $this->hasMany(SaleNote::class)->latest();
    }
}

