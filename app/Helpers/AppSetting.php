<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class AppSetting
{
    private static function path(): string
    {
        return storage_path('app/settings.json');
    }

    public static function all(): array
    {
        if (!file_exists(static::path())) {
            return static::defaults();
        }
        $data = json_decode(file_get_contents(static::path()), true);
        return array_merge(static::defaults(), $data ?? []);
    }

    public static function get(string $key, $default = null): mixed
    {
        return static::all()[$key] ?? $default;
    }

    public static function set(array $data): void
    {
        file_put_contents(
            static::path(),
            json_encode(array_merge(static::all(), $data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public static function defaults(): array
    {
        return [
            'name'          => 'Super Shop POS',
            'tagline'       => 'Multi-Branch Point of Sale',
            'address'       => 'Dhaka, Bangladesh',
            'phone'         => '',
            'email'         => '',
            'currency'      => '৳',
            'logo'          => null,
            'favicon'       => null,
            'footer_text'   => 'Thank you for shopping with us!',
            'primary_color' => '#2563eb',
            'seo_title'       => null,
            'seo_description' => null,
            'og_image'        => null,
            'og_image_path'   => null,
        ];
    }
}
