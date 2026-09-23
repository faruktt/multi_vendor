<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CourierSetting;
use App\Models\Sale;
use App\Models\SaleNote;
use App\Services\Courier\CourierManager;
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
                $sale->update(['courier_status' => $result['status']]);
            }

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status চেক ব্যর্থ হয়েছে: ' . $e->getMessage(),
            ], 500);
        }
    }
}
