<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CourierSetting;
use App\Models\Sale;
use App\Models\SaleNote;
use App\Services\Courier\CourierManager;
use App\Services\Courier\CourierStatusHandler;
use Illuminate\Http\Request;
use Exception;

class CourierOrderController extends Controller
{
    /**
     * Dispatch an order to an active courier
     */
    public function sendToCourier(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'courier_code'      => 'nullable|string',
            'recipient_name'    => 'required|string|max:150',
            'recipient_phone'   => 'required|string|max:30',
            'recipient_address' => 'required|string|max:500',
            'cod_amount'        => 'required|numeric|min:0',
            'note'              => 'nullable|string|max:500',
            'delivery_area'     => 'nullable|string|max:150',
        ]);

        // Find selected active courier or default active courier
        $courier = null;
        if (!empty($validated['courier_code'])) {
            $courier = CourierSetting::active()->where('code', $validated['courier_code'])->first();
        }

        if (!$courier) {
            $courier = CourierSetting::active()->first();
        }

        if (!$courier) {
            return response()->json([
                'success' => false,
                'message' => 'কোনো Courier সক্রিয় (Active) নেই। অনুগ্রহ করে Admin > Courier Settings থেকে কুরিয়ার একটিভ করুন।',
            ], 422);
        }

        try {
            $service = CourierManager::resolve($courier);
            $result  = $service->sendOrder($sale, $validated);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Courier এ অর্ডার পাঠাতে ব্যর্থ হয়েছে।',
                    'raw'     => $result['raw'] ?? null,
                ], 422);
            }

            // Update sale record
            $sale->update([
                'courier_name'           => $courier->code,
                'courier_tracking_code'  => $result['tracking_code'] ?? null,
                'courier_consignment_id' => $result['consignment_id'] ?? null,
                'courier_status'         => $result['status'] ?? 'pending',
                'courier_response'       => json_encode($result['raw'] ?? []),
                'courier_sent_at'        => now(),
                'order_status'           => 'sent_to_courier',
            ]);

            // Add note for history
            try {
                SaleNote::create([
                    'sale_id' => $sale->id,
                    'user_id' => auth()->id(),
                    'note'    => "Order sent to {$courier->name}. Tracking: " . ($result['tracking_code'] ?: 'N/A'),
                ]);
            } catch (Exception $e) {
                // ignore note logging failures
            }

            return response()->json([
                'success'        => true,
                'message'        => "{$courier->name}-এ সফলভাবে অর্ডার বুক করা হয়েছে!",
                'courier_name'   => $courier->name,
                'courier_code'   => $courier->code,
                'tracking_code'  => $result['tracking_code'] ?? '',
                'consignment_id' => $result['consignment_id'] ?? '',
                'status'         => $result['status'] ?? 'pending',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'সার্ভার ত্রুটি: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check live parcel status from courier
     */
    public function checkStatus(Sale $sale)
    {
        if (empty($sale->courier_name) || empty($sale->courier_tracking_code)) {
            return response()->json([
                'success' => false,
                'message' => 'এই অর্ডারে কোনো কুরিয়ার ট্র্যাকিং কোড পাওয়া যায়নি।',
            ], 422);
        }

        $courier = CourierSetting::byCode($sale->courier_name);
        if (!$courier) {
            return response()->json([
                'success' => false,
                'message' => 'কুরিয়ার কনফিগারেশন পাওয়া যায়নি।',
            ], 422);
        }

        try {
            $service = CourierManager::resolve($courier);
            $result  = $service->checkStatus($sale->courier_tracking_code);

            if ($result['success'] && !empty($result['status'])) {
                $handled = CourierStatusHandler::applyStatus($sale, $result['status'], $courier->name, $result['raw'] ?? null);

                return response()->json([
                    'success'              => true,
                    'status'               => $handled['courier_status'],
                    'courier_status'       => $handled['courier_status'],
                    'order_status'         => $handled['order_status'],
                    'order_status_changed' => $handled['order_status_changed'],
                    'is_cancelled'         => $handled['is_cancelled'],
                    'is_delivered'         => $handled['is_delivered'],
                    'message'              => $handled['message'],
                    'raw'                  => $result['raw'] ?? null,
                ]);
            }

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status চেক ব্যর্থ হয়েছে: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch sync live courier statuses for orders
     */
    public function syncBatch(Request $request)
    {
        $saleIds = $request->input('sale_ids', []);

        $query = Sale::withoutGlobalScopes()
            ->whereNotNull('courier_tracking_code')
            ->whereNotNull('courier_name');

        if (!empty($saleIds) && is_array($saleIds)) {
            $query->whereIn('id', $saleIds);
        } else {
            // Default: check active orders sent to courier that are not completed/cancelled
            $query->whereNotIn('order_status', ['completed', 'cancelled']);
        }

        $sales = $query->limit(50)->get();

        if ($sales->isEmpty()) {
            return response()->json([
                'success'   => true,
                'message'   => 'সিঙ্ক করার মতো কোনো সক্রিয় কুরিয়ার অর্ডার পাওয়া যায়নি।',
                'checked'   => 0,
                'cancelled' => 0,
                'updates'   => [],
            ]);
        }

        $checked = 0;
        $cancelledCount = 0;
        $updates = [];
        $courierSettings = CourierSetting::all()->keyBy('code');

        foreach ($sales as $sale) {
            $courier = $courierSettings->get($sale->courier_name);
            if (!$courier) continue;

            try {
                $service = CourierManager::resolve($courier);
                $res = $service->checkStatus($sale->courier_tracking_code);

                if ($res['success'] && !empty($res['status'])) {
                    $checked++;
                    $handled = CourierStatusHandler::applyStatus($sale, $res['status'], $courier->name, $res['raw'] ?? null);
                    if ($handled['is_cancelled']) {
                        $cancelledCount++;
                    }
                    $updates[] = [
                        'sale_id'              => $sale->id,
                        'invoice_no'           => $sale->invoice_no,
                        'courier_status'       => $handled['courier_status'],
                        'order_status'         => $handled['order_status'],
                        'order_status_changed' => $handled['order_status_changed'],
                        'is_cancelled'         => $handled['is_cancelled'],
                    ];
                }
            } catch (\Throwable $e) {
                // Continue to next order
            }
        }

        $msg = "{$checked} টি অর্ডারের কুরিয়ার স্ট্যাটাস চেক করা হয়েছে।";
        if ($cancelledCount > 0) {
            $msg .= " এর মধ্যে {$cancelledCount} টি ক্যানসেলড অর্ডার অটোমেটিক 'Cancelled' করা হয়েছে!";
        }

        return response()->json([
            'success'   => true,
            'message'   => $msg,
            'checked'   => $checked,
            'cancelled' => $cancelledCount,
            'updates'   => $updates,
        ]);
    }
}
