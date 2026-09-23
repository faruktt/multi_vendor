<?php

namespace App\Services\Courier;

use App\Models\CourierSetting;
use App\Models\Sale;
use Illuminate\Support\Facades\Http;
use Exception;

class SteadfastService implements CourierServiceInterface
{
    protected CourierSetting $setting;
    protected string $baseUrl;

    public function __construct(CourierSetting $setting)
    {
        $this->setting = $setting;
        $this->baseUrl = rtrim($setting->base_url ?: 'https://portal.steadfast.com.bd/api/v1', '/');
    }

    /**
     * Dispatch order to Steadfast Courier
     */
    public function sendOrder(Sale $sale, array $overrideData = []): array
    {
        if (empty($this->setting->api_key) || empty($this->setting->secret_key)) {
            return [
                'success' => false,
                'message' => 'Steadfast API Key বা Secret Key কনফিগার করা হয়নি। দয়া করে Courier Settings চেক করুন।',
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

        // If address is empty, combine district and thana
        if (empty($recipientAddress)) {
            $parts = array_filter([$sale->thana, $sale->district]);
            $recipientAddress = !empty($parts) ? implode(', ', $parts) : 'Customer Address';
        }

        // COD amount: defaults to due_amount. If paid or 0, fallback to 0 or total if specified
        $codAmount = isset($overrideData['cod_amount']) 
            ? (float)$overrideData['cod_amount'] 
            : (float)($sale->due_amount > 0 ? $sale->due_amount : ($sale->payment_status === 'paid' ? 0 : $sale->total));

        $invoice = $overrideData['invoice'] ?? $sale->invoice_no;
        $note = $overrideData['note'] ?? $sale->note ?? '';

        $payload = [
            'invoice'           => (string)$invoice,
            'recipient_name'    => $recipientName,
            'recipient_phone'   => $phone,
            'recipient_address' => $recipientAddress,
            'cod_amount'        => (int)round($codAmount),
            'note'              => $note ?: 'Order #' . $invoice,
        ];

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Api-Key'      => $this->setting->api_key,
                    'Secret-Key'   => $this->setting->secret_key,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->timeout(25)
                ->post($this->baseUrl . '/create_order', $payload);

            $data = $response->json();

            if ($response->successful() && isset($data['status']) && (int)$data['status'] === 200) {
                $consignment = $data['consignment'] ?? [];
                return [
                    'success'        => true,
                    'tracking_code'  => $consignment['tracking_code'] ?? ($consignment['consignment_id'] ?? null),
                    'consignment_id' => (string)($consignment['consignment_id'] ?? ($consignment['id'] ?? '')),
                    'status'         => $consignment['status'] ?? 'in_review',
                    'message'        => $data['message'] ?? 'Steadfast-এ অর্ডার সফলভাবে পাঠানো হয়েছে!',
                    'raw'            => $data,
                ];
            }

            // Extract error message
            $errorMsg = $data['message'] ?? ($data['errors'] ?? 'Steadfast API থেকে ত্রুটি এসেছে (Status ' . $response->status() . ')');
            if (is_array($errorMsg)) {
                $errorMsg = implode('; ', array_map(fn($v) => is_array($v) ? implode(', ', $v) : $v, $errorMsg));
            }

            return [
                'success' => false,
                'message' => 'Steadfast Error: ' . $errorMsg,
                'raw'     => $data,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Steadfast API সংযোগ ব্যর্থ হয়েছে: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check tracking status
     */
    public function checkStatus(string $trackingCode): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Api-Key'      => $this->setting->api_key,
                    'Secret-Key'   => $this->setting->secret_key,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->timeout(20)
                ->get($this->baseUrl . '/status_by_trackingcode/' . urlencode($trackingCode));

            $data = $response->json();

            if ($response->successful() && isset($data['status']) && (int)$data['status'] === 200) {
                return [
                    'success' => true,
                    'status'  => $data['delivery_status'] ?? 'unknown',
                    'message' => 'Status: ' . ($data['delivery_status'] ?? 'N/A'),
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
     * Test connection using get_balance endpoint
     */
    public function testConnection(): array
    {
        if (empty($this->setting->api_key) || empty($this->setting->secret_key)) {
            return [
                'success' => false,
                'message' => 'API Key এবং Secret Key দিতে হবে।',
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Api-Key'      => $this->setting->api_key,
                    'Secret-Key'   => $this->setting->secret_key,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->timeout(15)
                ->get($this->baseUrl . '/get_balance');

            $data = $response->json();

            if ($response->successful() && isset($data['status']) && (int)$data['status'] === 200) {
                $balance = $data['current_balance'] ?? 0;
                return [
                    'success' => true,
                    'message' => 'Steadfast সংযোগ সফল! বর্তমান ব্যালেন্স: ৳' . number_format($balance, 2),
                    'details' => $data,
                ];
            }

            $errMsg = $data['message'] ?? ('HTTP status ' . $response->status());
            return [
                'success' => false,
                'message' => 'Steadfast সংযোগ ব্যর্থ: ' . $errMsg,
                'details' => $data,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'সার্ভারে সংযোগ করা যায়নি: ' . $e->getMessage(),
            ];
        }
    }
}
