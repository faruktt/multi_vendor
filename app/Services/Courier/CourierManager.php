<?php

namespace App\Services\Courier;

use App\Models\CourierSetting;
use InvalidArgumentException;

class CourierManager
{
    /**
     * Resolve courier service instance by code or CourierSetting model
     */
    public static function resolve(string|CourierSetting $courier): CourierServiceInterface
    {
        if (is_string($courier)) {
            $setting = CourierSetting::byCode($courier);
            if (!$setting) {
                throw new InvalidArgumentException("Courier setting for '{$courier}' not found.");
            }
        } else {
            $setting = $courier;
        }

        return match (strtolower($setting->code)) {
            'steadfast' => new SteadfastService($setting),
            'pathao'    => new PathaoService($setting),
            'redx'      => new RedXService($setting),
            default     => throw new InvalidArgumentException("Unsupported courier code: '{$setting->code}'"),
        };
    }

    /**
     * Get list of currently active couriers
     */
    public static function getActiveCouriers()
    {
        return CourierSetting::active()->get();
    }

    /**
     * Get default active courier (first active)
     */
    public static function getDefaultActive(): ?CourierSetting
    {
        return CourierSetting::active()->first();
    }
}
