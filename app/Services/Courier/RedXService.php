<?php

namespace App\Services\Courier;

use App\Models\CourierSetting;
use App\Models\Sale;
use Illuminate\Support\Facades\Http;
use Exception;

class RedXService implements CourierServiceInterface
{
    protected CourierSetting $setting;
    protected string $baseUrl;

    public function __construct(CourierSetting $setting)
    {
        $this->setting = $setting;
        $this->baseUrl = rtrim($setting->base_url ?: 'https://openapi.redx.com.bd/v1.0.0', '/');
    }

    /**
     * Get RedX API token
     */
    protected function getToken(): string
    {
        $token = trim((string)($this->setting->api_key ?: $this->setting->secret_key));
        if (!empty($token) && !str_starts_with($token, 'Bearer ')) {
            return 'Bearer ' . $token;
        }
        return $token;
    }

    /**
     * Dispatch order to RedX Delivery
     */
    public function sendOrder(Sale $sale, array $overrideData = []): array
    {
        $token = $this->getToken();
        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'RedX API Access Token দেওয়া হয়নি। দয়া করে Courier Settings চেক করুন।',
            ];
        }

        // Phone normalization (11 digits)
        $phone = $overrideData['recipient_phone'] ?? $sale->customer?->phone ?? '';
        $phone = preg_replace('/[^\d]/', '', $phone);
        if (str_starts_with($phone, '880')) {
            $phone = substr($phone, 2);
        }
        if (strlen($phone) > 11) {
            $phone = substr($phone, -11);
        }

        $recipientName = trim($overrideData['recipient_name'] ?? $sale->customer?->name ?? 'Customer');
        $recipientAddress = trim($overrideData['recipient_address'] ?? $sale->customer?->address ?? '');
        $deliveryArea = trim($overrideData['delivery_area'] ?? $sale->district ?? $sale->thana ?? 'Dhaka');

        if (empty($recipientAddress)) {
            $parts = array_filter([$sale->thana, $sale->district]);
            $recipientAddress = !empty($parts) ? implode(', ', $parts) : 'Customer Address';
        }

        $codAmount = isset($overrideData['cod_amount']) 
            ? (float)$overrideData['cod_amount'] 
            : (float)($sale->due_amount > 0 ? $sale->due_amount : ($sale->payment_status === 'paid' ? 0 : $sale->total));

        $invoice = $overrideData['invoice'] ?? $sale->invoice_no;
        $note = $overrideData['note'] ?? $sale->note ?? '';

        $payload = [
            'customer_name'          => $recipientName,
            'customer_phone'         => $phone,
            'delivery_area'          => $deliveryArea ?: 'Dhaka',
            'customer_address'       => $recipientAddress,
            'merchant_invoice_id'    => (string)$invoice,
            'cash_collection_amount' => (int)round($codAmount),
            'parcel_weight'          => (int)($overrideData['parcel_weight'] ?? 500),
            'value'                  => (int)round($sale->total),
            'instruction'            => $note ?: 'Order #' . $invoice,
        ];

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'API-ACCESS-TOKEN' => $token,
                    'Content-Type'     => 'application/json',
                    'Accept'           => 'application/json',
                ])
                ->timeout(25)
                ->post($this->baseUrl . '/parcels', $payload);

            $data = $response->json();

            if ($response->successful() && !empty($data['tracking_id'])) {
                $trackingId = $data['tracking_id'];
                return [
                    'success'        => true,
                    'tracking_code'  => $trackingId,
                    'consignment_id' => $trackingId,
                    'status'         => 'ready_for_pickup',
                    'message'        => 'RedX-এ অর্ডার সফলভাবে তৈরি হয়েছে!',
                    'raw'            => $data,
                ];
            }

            $errorMsg = $data['message'] ?? ('RedX API Error (Status ' . $response->status() . ')');
            if (isset($data['errors']) && is_array($data['errors'])) {
                $errorMsg .= ': ' . json_encode($data['errors']);
            }

            return [
                'success' => false,
                'message' => 'RedX Error: ' . $errorMsg,
                'raw'     => $data,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'RedX API সংযোগ ব্যর্থ: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check parcel status
     */
    public function checkStatus(string $trackingCode): array
    {
        $token = $this->getToken();
        if (empty($token)) {
            return [
                'success' => false,
                'status'  => 'unknown',
                'message' => 'API Token missing',
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'API-ACCESS-TOKEN' => $token,
                    'Accept'           => 'application/json',
                ])
                ->timeout(20)
                ->get($this->baseUrl . '/parcels/' . urlencode($trackingCode));

            $data = $response->json();

            if ($response->successful()) {
                $status = $data['parcel']['status'] ?? ($data['status'] ?? 'unknown');
                return [
                    'success' => true,
                    'status'  => $status,
                    'message' => 'Status: ' . $status,
                    'raw'     => $data,
                ];
            }

            return [
                'success' => false,
                'status'  => 'unknown',
                'message' => $data['message'] ?? 'Status check failed',
                'raw'     => $data,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status'  => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test connection using /pickup_stores or /areas
     */
    public function testConnection(): array
    {
        $token = $this->getToken();
        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'RedX API Access Token প্রয়োজন।',
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'API-ACCESS-TOKEN' => $token,
                    'Accept'           => 'application/json',
                ])
                ->timeout(15)
                ->get($this->baseUrl . '/pickup_stores');

            $data = $response->json();

            if ($response->successful()) {
                $stores = $data['pickup_stores'] ?? [];
                $count = count($stores);
                return [
                    'success' => true,
                    'message' => "RedX সংযোগ সফল! {$count} টি পিকআপ স্টোর পাওয়া গেছে।",
                    'details' => $data,
                ];
            }

            $errMsg = $data['message'] ?? ('HTTP Status ' . $response->status());
            return [
                'success' => false,
                'message' => 'RedX সংযোগ ব্যর্থ: ' . $errMsg,
                'details' => $data,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'RedX সার্ভারে সংযোগ করা সম্ভব হয়নি: ' . $e->getMessage(),
            ];
        }
    }
}
