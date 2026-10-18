<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\Courier\CourierStatusHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CourierWebhookController extends Controller
{
    /**
     * Handle incoming courier webhook callbacks (Steadfast, Pathao, RedX, etc.)
     */
    public function handleWebhook(Request $request, ?string $courier = null)
    {
        $payload = $request->all();

        // If body is raw JSON or empty from all(), check json()
        if (empty($payload)) {
            $payload = $request->json()->all();
        }

        Log::info("Courier Webhook received [{$courier}]:", ['payload' => $payload]);

        // Support GET ping/verify requests from couriers
        if ($request->isMethod('get')) {
            return response()->json([
                'success' => true,
                'message' => 'Courier webhook endpoint is active.',
            ]);
        }

        // 1. Identify Tracking Code, Consignment ID, or Invoice
        $trackingCode = $payload['tracking_code'] 
            ?? $payload['tracking_id'] 
            ?? $payload['trackingCode'] 
            ?? null;

        $consignmentId = $payload['consignment_id'] 
            ?? $payload['consignmentId'] 
            ?? $payload['parcel_id'] 
            ?? null;

        $invoiceNo = $payload['invoice'] 
            ?? $payload['invoice_no'] 
            ?? $payload['merchant_order_id'] 
            ?? $payload['invoice_id'] 
            ?? $payload['order_id'] 
            ?? null;

        // 2. Identify Status
        $courierStatus = $payload['status'] 
            ?? $payload['delivery_status'] 
            ?? $payload['order_status'] 
            ?? $payload['parcel_status'] 
            ?? null;

        // If status is in a nested data or parcel object
        if (empty($courierStatus) && isset($payload['data']['order_status'])) {
            $courierStatus = $payload['data']['order_status'];
        }
        if (empty($courierStatus) && isset($payload['parcel']['status'])) {
            $courierStatus = $payload['parcel']['status'];
        }

        if (empty($courierStatus)) {
            return response()->json([
                'success' => false,
                'message' => 'Courier status not found in payload.',
            ], 400);
        }

        // 3. Find matching Sale record without global scopes
        $sale = null;

        if (!empty($trackingCode)) {
            $sale = Sale::withoutGlobalScopes()
                ->where('courier_tracking_code', $trackingCode)
                ->first();
        }

        if (!$sale && !empty($consignmentId)) {
            $sale = Sale::withoutGlobalScopes()
                ->where('courier_consignment_id', (string)$consignmentId)
                ->orWhere('courier_tracking_code', (string)$consignmentId)
                ->first();
        }

        if (!$sale && !empty($invoiceNo)) {
            $sale = Sale::withoutGlobalScopes()
                ->where('invoice_no', $invoiceNo)
                ->first();

            // Try matching integer ID if invoice format is numeric
            if (!$sale && is_numeric($invoiceNo)) {
                $sale = Sale::withoutGlobalScopes()->find((int)$invoiceNo);
            }
        }

        if (!$sale) {
            Log::warning("Courier Webhook: No sale found for payload", [
                'courier'        => $courier,
                'tracking_code'  => $trackingCode,
                'consignment_id' => $consignmentId,
                'invoice'        => $invoiceNo,
                'status'         => $courierStatus,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No matching sale found for the given parcel/invoice.',
            ], 404);
        }

        // 4. Apply status and auto-cancel if courier cancelled/returned
        $courierName = $courier ?: $sale->courier_name ?: 'Courier';
        $result = CourierStatusHandler::applyStatus($sale, (string)$courierStatus, $courierName, $payload);

        Log::info("Courier Webhook processed successfully for Sale #{$sale->id} ({$sale->invoice_no}):", $result);

        return response()->json([
            'success'              => true,
            'message'              => 'Webhook processed successfully.',
            'sale_id'              => $sale->id,
            'invoice_no'           => $sale->invoice_no,
            'courier_status'       => $result['courier_status'],
            'order_status'         => $result['order_status'],
            'order_status_changed' => $result['order_status_changed'],
            'is_cancelled'         => $result['is_cancelled'],
        ]);
    }
}
