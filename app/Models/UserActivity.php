<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserActivity extends Model
{
    protected $table = 'user_activities';

    protected $fillable = [
        'session_id',
        'ip_address',
        'method',
        'path',
        'url',
        'page_name',
        'user_type',
        'user_id',
        'device',
        'platform',
        'browser',
        'referer',
        'user_agent',
    ];

    /**
     * Record a page visit from an incoming request.
     */
    public static function recordVisit(Request $request): ?self
    {
        $rawPath = trim($request->path(), '/');
        $path    = $rawPath === '' ? '/' : '/' . $rawPath;

        // Determine user authentication type
        $userType = 'guest';
        $userId   = null;

        if (auth('customer')->check()) {
            $userType = 'customer';
            $userId   = auth('customer')->id();
        } elseif (auth('reseller')->check()) {
            $userType = 'reseller';
            $userId   = auth('reseller')->id();
        } elseif (auth('supplier')->check()) {
            $userType = 'supplier';
            $userId   = auth('supplier')->id();
        } elseif (auth()->check()) {
            $userType = 'admin';
            $userId   = auth()->id();
        }

        $userAgent = (string) $request->userAgent();
        $deviceInfo = self::parseUserAgent($userAgent);

        $pageName = self::resolvePageName($request, $path);

        return self::create([
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'ip_address' => $request->ip(),
            'method'     => $request->method(),
            'path'       => Str::limit($path, 250, ''),
            'url'        => Str::limit($request->fullUrl(), 500, ''),
            'page_name'  => Str::limit($pageName, 145, ''),
            'user_type'  => $userType,
            'user_id'    => $userId,
            'device'     => $deviceInfo['device'],
            'platform'   => $deviceInfo['platform'],
            'browser'    => $deviceInfo['browser'],
            'referer'    => Str::limit($request->header('referer'), 500, ''),
            'user_agent' => Str::limit($userAgent, 500, ''),
        ]);
    }

    /**
     * Parse User Agent string into device, platform, and browser.
     */
    public static function parseUserAgent(?string $ua): array
    {
        if (empty($ua)) {
            return ['device' => 'Unknown', 'platform' => 'Unknown', 'browser' => 'Unknown'];
        }

        // Device detection
        $device = 'Desktop';
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
            $device = 'Tablet';
        } elseif (preg_match('/(mobi|iphone|ipod|blackberry|opera mini|iemobile|mobile)/i', $ua)) {
            $device = 'Mobile';
        }

        // Platform detection
        $platform = 'Unknown';
        if (preg_match('/windows|win32/i', $ua)) {
            $platform = 'Windows';
        } elseif (preg_match('/android/i', $ua)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $platform = 'iOS';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $platform = 'Linux';
        }

        // Browser detection
        $browser = 'Other';
        if (preg_match('/edg/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome|crios/i', $ua) && !preg_match('/opr|opera/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox|fxios/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome|crios/i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/opr|opera/i', $ua)) {
            $browser = 'Opera';
        }

        return [
            'device'   => $device,
            'platform' => $platform,
            'browser'  => $browser,
        ];
    }

    /**
     * Resolve a readable page name from the request and path.
     */
    public static function resolvePageName(Request $request, string $path): string
    {
        $routeName = $request->route()?->getName();

        if ($path === '/' || $path === '/shop') {
            return 'Homepage';
        }

        if ($routeName === 'shop.products.show') {
            $slug = $request->route('productSlug');
            return 'Product: ' . Str::headline($slug ?? 'Detail');
        }

        if ($routeName === 'shop.products.category') {
            $slug = $request->route('categorySlug');
            return 'Category: ' . Str::headline($slug ?? 'List');
        }

        if ($routeName === 'shop.products.index') {
            return 'All Products / Search';
        }

        if ($routeName === 'shop.checkout.index') {
            return 'Checkout Page';
        }

        if ($routeName === 'shop.checkout.success') {
            return 'Order Confirmation';
        }

        if ($routeName === 'shop.track.index') {
            return 'Order Tracking';
        }

        if (Str::startsWith($path, '/reseller')) {
            if (Str::contains($path, 'login')) return 'Reseller: Login';
            if (Str::contains($path, 'register')) return 'Reseller: Registration';
            if (Str::contains($path, 'dashboard')) return 'Reseller: Dashboard';
            return 'Reseller Portal';
        }

        if (Str::startsWith($path, '/supplier')) {
            if (Str::contains($path, 'login')) return 'Supplier: Login';
            if (Str::contains($path, 'register')) return 'Supplier: Registration';
            if (Str::contains($path, 'dashboard')) return 'Supplier: Dashboard';
            return 'Supplier Portal';
        }

        if (Str::startsWith($path, '/customer')) {
            if (Str::contains($path, 'login')) return 'Customer: Login';
            if (Str::contains($path, 'register')) return 'Customer: Register';
            if (Str::contains($path, 'account') || Str::contains($path, 'dashboard')) return 'Customer: Account';
            return 'Customer Portal';
        }

        if ($routeName) {
            return Str::headline(str_replace(['shop.', 'branch.', '.'], ['', '', ' '], $routeName));
        }

        return Str::headline(trim($path, '/'));
    }
}
