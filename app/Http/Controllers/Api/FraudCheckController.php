<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FraudCheckController extends Controller
{
    public function check(Request $request)
    {
        $phone = trim($request->query('phone', ''));

        if (!$phone) {
            return response()->json(['error' => 'Phone number is required.'], 422);
        }

        $apiKey = config('services.fraud_checker.key');
        $url    = config('services.fraud_checker.url');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->withOptions(['verify' => false])->asForm()->post($url, [
            'phone' => $phone,
        ]);

        if ($response->status() === 403) {
            $msg = $response->json('message') ?? 'API subscription expired or inactive.';
            return response()->json(['error' => $msg], 403);
        }

        if (!$response->successful()) {
            return response()->json(['error' => 'Fraud checker service unavailable. Please try again.'], 503);
        }

        $data = $response->json();

        $total     = (int) ($data['total_parcels']   ?? 0);
        $delivered = (int) ($data['total_delivered'] ?? 0);
        $cancelled = (int) ($data['total_cancel']    ?? 0);
        $pending   = max(0, $total - $delivered - $cancelled);

        $cancelRate = $total > 0 ? round(($cancelled / $total) * 100, 1) : 0;

        if ($total === 0) {
            $risk  = 'unknown';
            $label = 'No data found for this number.';
        } elseif ($cancelRate >= 40) {
            $risk  = 'high';
            $label = 'High cancellation rate. Likely a fraud customer — avoid COD.';
        } elseif ($cancelRate >= 20) {
            $risk  = 'medium';
            $label = 'Moderate cancellations. Proceed with caution.';
        } else {
            $risk  = 'low';
            $label = 'Good delivery record. Trustworthy customer.';
        }

        return response()->json([
            'phone'          => $phone,
            'total_parcels'  => $total,
            'total_delivered'=> $delivered,
            'total_cancel'   => $cancelled,
            'total_pending'  => $pending,
            'cancel_rate'    => $cancelRate,
            'risk'           => $risk,
            'label'          => $label,
            'raw'            => $data,
        ]);
    }
}
