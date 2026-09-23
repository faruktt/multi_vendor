<?php

namespace App\Services\Courier;

use App\Models\Sale;

interface CourierServiceInterface
{
    /**
     * Dispatch / create an order in the courier service
     *
     * @param Sale $sale
     * @param array $overrideData [recipient_name, recipient_phone, recipient_address, cod_amount, note, etc.]
     * @return array [
     *     'success'         => bool,
     *     'tracking_code'   => ?string,
     *     'consignment_id'  => ?string,
     *     'status'          => ?string,
     *     'message'         => string,
     *     'raw'             => mixed,
     * ]
     */
    public function sendOrder(Sale $sale, array $overrideData = []): array;

    /**
     * Check tracking / parcel status by tracking code or consignment id
     *
     * @param string $trackingCode
     * @return array [
     *     'success' => bool,
     *     'status'  => string,
     *     'message' => string,
     *     'raw'     => mixed,
     * ]
     */
    public function checkStatus(string $trackingCode): array;

    /**
     * Test connection / credentials with the courier API
     *
     * @return array ['success' => bool, 'message' => string, 'details' => mixed]
     */
    public function testConnection(): array;
}
