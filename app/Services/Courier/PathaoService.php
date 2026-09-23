<?php

namespace App\Services\Courier;

use App\Models\CourierSetting;
use App\Models\Sale;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Exception;

class PathaoService implements CourierServiceInterface
{
    protected CourierSetting $setting;
    protected string $baseUrl;

    public function __construct(CourierSetting $setting)
    {
        $this->setting = $setting;
        $this->baseUrl = rtrim($setting->base_url ?: 'https://api-hermes.pathao.com', '/');
    }

    /**
     * Get client secret from secret_key or client_secret attribute
     */
    protected function getClientSecret(): ?string
    {
        return $this->setting->secret_key ?: ($this->setting->client_secret ?? null);
    }

    /**
     * Get or refresh Pathao access token
     */
    public function getAccessToken(?string &$errorDetail = null, bool $forceFresh = false): ?string
    {
        // Check if pre-configured bearer token in api_key
        if (!empty($this->setting->api_key) && empty($this->setting->client_id)) {
            return trim($this->setting->api_key);
        }

        $cacheKey = 'pathao_courier_token_' . $this->setting->id;
        if ($forceFresh) {
            Cache::forget($cacheKey);
        } else {
            $cachedToken = Cache::get($cacheKey);
            if ($cachedToken) {
                return $cachedToken;
            }
        }

        $secret = $this->getClientSecret();
        if (empty($this->setting->client_id) || empty($secret) || empty($this->setting->username) || empty($this->setting->password)) {
            $missing = [];
            if (empty($this->setting->client_id)) $missing[] = 'Client ID';
            if (empty($secret)) $missing[] = 'Secret Key / Client Secret';
            if (empty($this->setting->username)) $missing[] = 'Username (Email)';
            if (empty($this->setting->password)) $missing[] = 'Password';
            $errorDetail = 'প্রয়োজনীয় তথ্য অনুপস্থিত: ' . implode(', ', $missing);
            return null;
        }

        try {
            $response = Http::withoutVerifying()
                ->asJson()
                ->acceptJson()
                ->timeout(20)
                ->post($this->baseUrl . '/aladdin/api/v1/issue-token', [
                    'client_id'     => $this->setting->client_id,
                    'client_secret' => $secret,
                    'username'      => $this->setting->username,
                    'password'      => $this->setting->password,
                    'grant_type'    => 'password',
                ]);

            $data = $response->json();

            if ($response->successful()) {
                $token = $data['access_token'] ?? null;
                $expiresIn = (int)($data['expires_in'] ?? 86400);

                if ($token) {
                    Cache::put($cacheKey, $token, max(60, $expiresIn - 300));
                    return $token;
                }
            }

            $errorDetail = $data['message'] ?? ($data['error_description'] ?? ($data['error'] ?? 'Pathao API ত্রুটি (HTTP ' . $response->status() . ')'));
        } catch (Exception $e) {
            $errorDetail = 'সার্ভার সংযোগ ব্যর্থ: ' . $e->getMessage();
        }

        return null;
    }

    /**
     * Fetch user's stores list from Pathao
     */
    public function getStores(): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return [];
        }

        try {
            $response = Http::withoutVerifying()
                ->withToken($token)
                ->acceptJson()
                ->timeout(15)
                ->get($this->baseUrl . '/aladdin/api/v1/stores');

            if ($response->successful()) {
                $data = $response->json();
                return $data['data']['data'] ?? ($data['data'] ?? []);
            }
        } catch (Exception $e) {
            // ignore
        }

        return [];
    }

    /**
     * Resolve City ID and Zone ID for Pathao
     */
    public function resolveCityAndZone(?string $districtName = null, ?string $thanaName = null): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['city_id' => 1, 'zone_id' => 298];
        }

        // Cache cities list for 24 hours
        $cities = Cache::remember('pathao_cities_list', 86400, function () use ($token) {
            try {
                $res = Http::withoutVerifying()
                    ->withToken($token)
                    ->acceptJson()
                    ->timeout(15)
                    ->get($this->baseUrl . '/aladdin/api/v1/city-list');
                return $res->json()['data']['data'] ?? ($res->json()['data'] ?? []);
            } catch (Exception $e) {
                return [];
            }
        });

        $cityId = 1; // Default Dhaka
        $targetDistrict = strtolower(trim((string)$districtName));
        if (!empty($targetDistrict)) {
            foreach ($cities as $c) {
                $cityName = strtolower(trim((string)($c['city_name'] ?? '')));
                if ($cityName === $targetDistrict || str_contains($cityName, $targetDistrict) || str_contains($targetDistrict, $cityName)) {
                    $cityId = (int)$c['city_id'];
                    break;
                }
            }
        }

        // Cache zones for this city
        $zones = Cache::remember('pathao_zones_city_' . $cityId, 86400, function () use ($token, $cityId) {
            try {
                $res = Http::withoutVerifying()
                    ->withToken($token)
                    ->acceptJson()
                    ->timeout(15)
                    ->get($this->baseUrl . "/aladdin/api/v1/cities/{$cityId}/zone-list");
                return $res->json()['data']['data'] ?? ($res->json()['data'] ?? []);
            } catch (Exception $e) {
                return [];
            }
        });

        $zoneId = 298; // fallback (Dhaka zone)
        if (!empty($zones)) {
            $zoneId = (int)$zones[0]['zone_id'];
            $targetThana = strtolower(trim((string)$thanaName));
            if (!empty($targetThana)) {
                foreach ($zones as $z) {
                    $zoneName = strtolower(trim((string)($z['zone_name'] ?? '')));
                    if ($zoneName === $targetThana || str_contains($zoneName, $targetThana) || str_contains($targetThana, $zoneName)) {
                        $zoneId = (int)$z['zone_id'];
                        break;
                    }
                }
            }
        }

        return ['city_id' => $cityId, 'zone_id' => $zoneId];
    }

    /**
     * Dispatch order to Pathao Courier
     */
    public function sendOrder(Sale $sale, array $overrideData = []): array
    {
        $errorDetail = null;
        $token = $this->getAccessToken($errorDetail);
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Pathao অ্যাক্সেস টোকেন পাওয়া যায়নি: ' . ($errorDetail ?: 'Client ID, Secret, Username ও Password চেক করুন।'),
            ];
        }

        // Determine store_id
        $storeId = $this->setting->store_id;
        if (empty($storeId)) {
            $stores = $this->getStores();
            if (!empty($stores) && isset($stores[0]['store_id'])) {
                $storeId = $stores[0]['store_id'];
            }
        }

        if (empty($storeId)) {
            return [
                'success' => false,
                'message' => 'Pathao Store ID পাওয়া যায়নি। দয়া করে Courier Settings-এ Store ID যুক্ত করুন।',
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
        if (empty($recipientAddress)) {
            $parts = array_filter([$sale->thana, $sale->district]);
            $recipientAddress = !empty($parts) ? implode(', ', $parts) : 'Customer Address';
        }

        $codAmount = isset($overrideData['cod_amount']) 
            ? (float)$overrideData['cod_amount'] 
            : (float)($sale->due_amount > 0 ? $sale->due_amount : ($sale->payment_status === 'paid' ? 0 : $sale->total));

        $invoice = $overrideData['invoice'] ?? $sale->invoice_no;
        $note = $overrideData['note'] ?? $sale->note ?? '';

        // Dynamically resolve Pathao City and Zone based on customer district and thana
        $cityZone = $this->resolveCityAndZone($overrideData['district'] ?? $sale->district, $overrideData['thana'] ?? $sale->thana);
        $cityId   = (int)($overrideData['city_id'] ?? $cityZone['city_id']);
        $zoneId   = (int)($overrideData['zone_id'] ?? $cityZone['zone_id']);
        $areaId   = isset($overrideData['area_id']) ? (int)$overrideData['area_id'] : null;

        $payload = [
            'store_id'            => (int)$storeId,
            'merchant_order_id'   => (string)$invoice,
            'recipient_name'      => $recipientName,
            'recipient_phone'     => $phone,
            'recipient_address'   => $recipientAddress,
            'recipient_city'      => $cityId,
            'recipient_zone'      => $zoneId,
            'delivery_type'       => (int)($this->setting->delivery_type ?: 48), // 48 = normal
            'item_type'           => 2, // 2 = Parcel
            'special_instruction' => $note ?: 'Order #' . $invoice,
            'item_quantity'       => 1,
            'item_weight'         => 0.5,
            'amount_to_collect'   => (int)round($codAmount),
        ];

        if ($areaId) {
            $payload['recipient_area'] = $areaId;
        }

        try {
            $response = Http::withoutVerifying()
                ->withToken($token)
                ->acceptJson()
                ->timeout(25)
                ->post($this->baseUrl . '/aladdin/api/v1/orders', $payload);

            $data = $response->json();

            if ($response->successful() && ($data['type'] ?? '') === 'success') {
                $orderData = $data['data'] ?? [];
                $consignmentId = $orderData['consignment_id'] ?? ($orderData['order_id'] ?? null);

                return [
                    'success'        => true,
                    'tracking_code'  => $consignmentId,
                    'consignment_id' => (string)$consignmentId,
                    'status'         => $orderData['order_status'] ?? 'pending',
                    'message'        => $data['message'] ?? 'Pathao-তে অর্ডার সফলভাবে তৈরি হয়েছে!',
                    'raw'            => $data,
                ];
            }

            $errorMsg = $data['message'] ?? ('Pathao API Error (Status ' . $response->status() . ')');
            if (!empty($data['errors'])) {
                $errDetails = is_array($data['errors']) 
                    ? implode('; ', array_map(fn($v) => is_array($v) ? implode(', ', $v) : $v, $data['errors']))
                    : $data['errors'];
                $errorMsg .= ' - ' . $errDetails;
            }

            return [
                'success' => false,
                'message' => 'Pathao Error: ' . $errorMsg,
                'raw'     => $data,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Pathao API সংযোগ ব্যর্থ: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check order status
     */
    public function checkStatus(string $trackingCode): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return [
                'success' => false,
                'status'  => 'unknown',
                'message' => 'Authentication failed',
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->withToken($token)
                ->acceptJson()
                ->timeout(20)
                ->get($this->baseUrl . '/aladdin/api/v1/orders/' . urlencode($trackingCode) . '/info');

            $data = $response->json();

            if ($response->successful() && ($data['type'] ?? '') === 'success') {
                $orderData = $data['data'] ?? [];
                return [
                    'success' => true,
                    'status'  => $orderData['order_status'] ?? 'unknown',
                    'message' => 'Status: ' . ($orderData['order_status'] ?? 'N/A'),
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
     * Test connection
     */
    public function testConnection(): array
    {
        $errorDetail = null;
        $token = $this->getAccessToken($errorDetail, true);
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Pathao টোকেন সংগ্রহ ব্যর্থ হয়েছে: ' . ($errorDetail ?: 'Client ID, Secret, Username ও Password চেক করুন।'),
            ];
        }

        $stores = $this->getStores();
        $storeCount = count($stores);
        $storeNames = implode(', ', array_slice(array_column($stores, 'store_name'), 0, 3));

        return [
            'success' => true,
            'message' => "Pathao সংযোগ সফল! {$storeCount} টি পিকআপ স্টোর পাওয়া গেছে" . ($storeNames ? " ({$storeNames})" : ''),
            'details' => ['stores' => $stores],
        ];
    }
}
