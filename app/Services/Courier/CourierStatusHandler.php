<?php

namespace App\Services\Courier;

use App\Models\Sale;
use App\Models\SaleNote;
use Illuminate\Support\Facades\Log;

class CourierStatusHandler
{
    /**
     * Keywords that indicate courier cancellation, failure, or customer rejection/return
     */
    protected static array $cancelledKeywords = [
        'cancel',
        'cancelled',
        'canceled',
        'cancelled_approval_pending',
        'pickup_cancelled',
        'delivery_cancelled',
        'delivery-cancelled',
        'return',
        'returned',
        'parcel_returned',
        'parcel-returned',
        'delivery_failed',
        'failed',
        'undelivered',
        'refused',
        'rejected',
        'customer_reject',
        'customer_unreachable',
        'damage',
        'lost',
    ];

    /**
     * Keywords that indicate parcel was successfully delivered
     */
    protected static array $deliveredKeywords = [
        'delivered',
        'partial_delivered',
        'partial-delivered',
        'partially_delivered',
    ];

    /**
     * Check if a courier status indicates cancellation or customer return
     */
    public static function isCancelledOrReturned(?string $status): bool
    {
        if (empty($status)) {
            return false;
        }

        $s = strtolower(trim($status));
        $normalized = str_replace(['-', ' '], '_', $s);

        foreach (self::$cancelledKeywords as $keyword) {
            $kwNormalized = str_replace(['-', ' '], '_', $keyword);
            if ($normalized === $kwNormalized || str_contains($normalized, $kwNormalized)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if courier status is delivered
     */
    public static function isDelivered(?string $status): bool
    {
        if (empty($status)) {
            return false;
        }

        $s = strtolower(trim($status));
        $normalized = str_replace(['-', ' '], '_', $s);

        foreach (self::$deliveredKeywords as $keyword) {
            $kwNormalized = str_replace(['-', ' '], '_', $keyword);
            if ($normalized === $kwNormalized || str_contains($normalized, $kwNormalized)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply courier status and update sale & order_status accordingly
     */
    public static function applyStatus(Sale $sale, string $courierStatus, ?string $courierName = null, ?array $raw = null): array
    {
        $courierName = $courierName ?: $sale->courier_display_name ?: ($sale->courier_name ?: 'Courier');
        $oldOrderStatus = $sale->order_status;
        $oldCourierStatus = $sale->courier_status;

        $updates = [
            'courier_status' => $courierStatus,
        ];

        if (!empty($raw)) {
            $updates['courier_response'] = json_encode($raw);
        }

        $orderStatusChanged = false;
        $newOrderStatus = $oldOrderStatus;
        $message = "কুরিয়ার স্ট্যাটাস: " . str_replace('_', ' ', $courierStatus);

        // Auto-Cancel if courier returned / cancelled the order
        if (self::isCancelledOrReturned($courierStatus)) {
            $newOrderStatus = 'cancelled';
            $updates['order_status'] = 'cancelled';
            $orderStatusChanged = ($oldOrderStatus !== 'cancelled');
            $message = "কুরিয়ার অর্ডারটি বাতিল/রিটার্ন করেছে ({$courierStatus})। ওয়েবসাইট স্ট্যাটাস অটোমেটিক 'Cancelled' করা হয়েছে।";
        } elseif (self::isDelivered($courierStatus)) {
            // If delivered by courier, mark delivered if not already completed/delivered
            if (in_array($oldOrderStatus, ['sent_to_courier', 'out_for_delivery', 'processing'])) {
                $newOrderStatus = 'delivered';
                $updates['order_status']   = 'delivered';
                $updates['payment_status'] = 'paid';
                $updates['paid_amount']    = $sale->total;
                $updates['due_amount']     = 0;
                $orderStatusChanged = true;
                $message = "কুরিয়ার পার্সেল ডেলিভার করেছে। ওয়েবসাইট অর্ডার স্ট্যাটাস 'Delivered' এবং পেমেন্ট 'Paid' করা হয়েছে।";
            }
        }

        $sale->update($updates);

        if ($orderStatusChanged) {
            try {
                SaleNote::create([
                    'sale_id' => $sale->id,
                    'user_id' => auth()->id() ?? null,
                    'note'    => "Courier ({$courierName}) status updated to: '{$courierStatus}'. Order status automatically updated from '{$oldOrderStatus}' to '{$newOrderStatus}'.",
                ]);
            } catch (\Throwable $e) {
                Log::warning("CourierStatusHandler: Failed to create SaleNote for sale #{$sale->id}: " . $e->getMessage());
            }
        }

        return [
            'success'              => true,
            'sale_id'              => $sale->id,
            'invoice_no'           => $sale->invoice_no,
            'courier_name'         => $courierName,
            'courier_status'       => $courierStatus,
            'old_order_status'     => $oldOrderStatus,
            'order_status'         => $newOrderStatus,
            'order_status_changed' => $orderStatusChanged,
            'is_cancelled'         => ($newOrderStatus === 'cancelled'),
            'is_delivered'         => ($newOrderStatus === 'delivered'),
            'message'              => $message,
        ];
    }
}
