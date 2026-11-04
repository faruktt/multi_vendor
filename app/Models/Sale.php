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

        static::saving(function ($sale) {
            if (in_array(strtolower((string) $sale->order_status), ['delivered', 'completed', 'complete'])) {
                $sale->payment_status = 'paid';
                $sale->paid_amount    = $sale->total;
                $sale->due_amount     = 0;
            }
        });

        static::saved(function ($sale) {
            $cancelledOrReturned = ['cancelled', 'cancel', 'return', 'returned'];
            $currentStatus  = strtolower((string) $sale->order_status);
            $originalStatus = strtolower((string) $sale->getOriginal('order_status'));

            if ($sale->wasChanged('order_status')) {
                if (in_array($currentStatus, $cancelledOrReturned) && !in_array($originalStatus, $cancelledOrReturned)) {
                    $sale->restoreStock("Stock restored — order #{$sale->invoice_no} status changed to {$sale->order_status}");
                } elseif (!in_array($currentStatus, $cancelledOrReturned) && in_array($originalStatus, $cancelledOrReturned)) {
                    $sale->reDeductStock("Stock re-deducted — order #{$sale->invoice_no} status changed from {$originalStatus} to {$sale->order_status}");
                }
            }
        });
    }

    /**
     * Restore stock to inventory when order is cancelled or returned.
     */
    public function restoreStock(string $reason = ''): void
    {
        // Check if stock has already been restored for this sale
        $alreadyRestored = StockMovement::whereIn('reference_type', ['sale_cancel', 'sale_return'])
            ->where('reference_id', $this->id)
            ->exists();

        if ($alreadyRestored) {
            return;
        }

        $items = $this->saleItems()->with(['product', 'variant'])->get();
        foreach ($items as $item) {
            $qty = (int) $item->quantity;
            if ($qty <= 0) continue;

            // Restore product stock
            if ($item->product_id) {
                Product::withoutGlobalScopes()
                    ->where('id', $item->product_id)
                    ->increment('stock_qty', $qty);
            }

            // Restore variant stock
            if ($item->product_variant_id) {
                ProductVariant::where('id', $item->product_variant_id)
                    ->increment('stock_qty', $qty);
            }

            // Record stock movement
            StockMovement::create([
                'vendor_id'      => $this->vendor_id,
                'product_id'     => $item->product_id,
                'type'           => 'in',
                'quantity'       => $qty,
                'reference_type' => 'sale_cancel',
                'reference_id'   => $this->id,
                'note'           => $reason ?: "Stock restored — order #{$this->invoice_no} cancelled/returned",
            ]);
        }
    }

    /**
     * Re-deduct stock if a cancelled/returned order is reopened.
     */
    public function reDeductStock(string $reason = ''): void
    {
        $hasRestored = StockMovement::whereIn('reference_type', ['sale_cancel', 'sale_return'])
            ->where('reference_id', $this->id)
            ->exists();

        if (!$hasRestored) {
            return;
        }

        $items = $this->saleItems()->with(['product', 'variant'])->get();
        foreach ($items as $item) {
            $qty = (int) $item->quantity;
            if ($qty <= 0) continue;

            if ($item->product_id) {
                Product::withoutGlobalScopes()
                    ->where('id', $item->product_id)
                    ->decrement('stock_qty', $qty);
            }

            if ($item->product_variant_id) {
                ProductVariant::where('id', $item->product_variant_id)
                    ->decrement('stock_qty', $qty);
            }

            StockMovement::create([
                'vendor_id'      => $this->vendor_id,
                'product_id'     => $item->product_id,
                'type'           => 'out',
                'quantity'       => $qty,
                'reference_type' => 'sale',
                'reference_id'   => $this->id,
                'note'           => $reason ?: "Stock re-deducted — order #{$this->invoice_no} reopened",
            ]);
        }

        StockMovement::whereIn('reference_type', ['sale_cancel', 'sale_return'])
            ->where('reference_id', $this->id)
            ->delete();
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

    /**
     * Scope query to sales eligible for revenue reports and price calculations.
     * POS orders (not cancelled) are immediately eligible.
     * Non-POS orders (website, reseller, etc.) are only eligible when status is 'completed' or 'complete'.
     */
    public function scopeCompletedOrPos($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($posQ) {
                $posQ->where('channel', 'pos')
                     ->whereNotIn('order_status', ['cancelled']);
            })->orWhereIn('order_status', ['completed', 'complete']);
        });
    }

    /**
     * Check if this sale model is eligible for revenue and financial reports.
     */
    public function isEligibleForRevenue(): bool
    {
        if (strtolower((string)$this->channel) === 'pos') {
            return strtolower((string)$this->order_status) !== 'cancelled';
        }
        return in_array(strtolower((string)$this->order_status), ['completed', 'complete']);
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

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class);
    }

    public function getResellerCouponDiscountAttribute(): float
    {
        return (float) ($this->couponUsage?->discount_amount ?? 0);
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

