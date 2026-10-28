<?php

namespace App\Services\Security;

use App\Helpers\AppSetting;
use App\Mail\AdminFailedLoginAlertMail;
use App\Models\BlockedIp;
use App\Models\LoginAttemptLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class IpSecurityService
{
    /**
     * Resolve approximate geo-location for an IP.
     */
    public static function resolveLocation(string $ip): string
    {
        if ($ip === '127.0.0.1' || $ip === '::1' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return 'Localhost / Private Network';
        }

        return Cache::remember("ip_location_{$ip}", 86400, function () use ($ip) {
            try {
                $response = Http::timeout(2.5)->get("http://ip-api.com/json/{$ip}");
                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'success') {
                        $parts = array_filter([
                            $data['city'] ?? null,
                            $data['regionName'] ?? null,
                            $data['country'] ?? null,
                            !empty($data['isp']) ? '(' . $data['isp'] . ')' : null,
                        ]);
                        return implode(', ', $parts) ?: 'Unknown Location';
                    }
                }
            } catch (\Throwable $e) {
                Log::debug("IP Location lookup failed for {$ip}: " . $e->getMessage());
            }

            return 'Unknown Location';
        });
    }

    /**
     * Record a failed admin login attempt.
     * When it hits 2 attempts, sends a security alert email with a 1-click block button.
     */
    public function recordFailedAdminAttempt(Request $request, ?string $email = null): void
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $location = self::resolveLocation($ip);

        // 1. Log attempt to database
        try {
            LoginAttemptLog::create([
                'ip_address' => $ip,
                'email'      => $email,
                'user_agent' => $userAgent,
                'location'   => $location,
                'is_success' => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning("Failed to log login attempt: " . $e->getMessage());
        }

        // 2. Track failed count in Cache
        $failKey = "admin_login_fails_{$ip}";
        $currentFails = (int) Cache::get($failKey, 0) + 1;
        Cache::put($failKey, $currentFails, now()->addHours(24));

        // 3. If reaches 2 or more attempts, trigger security email alert
        if ($currentFails >= 2) {
            $alertKey = "admin_login_alert_sent_{$ip}";
            
            // Send alert if not already sent in the last 2 hours for this IP
            if (!Cache::has($alertKey)) {
                Cache::put($alertKey, true, now()->addHours(2));

                $this->sendSecurityAlertEmail($ip, $email, $location, $userAgent, $currentFails);
            }
        }
    }

    /**
     * Record successful login attempt and reset fail counter.
     */
    public function recordSuccessfulAdminAttempt(Request $request, string $email): void
    {
        $ip = $request->ip();
        Cache::forget("admin_login_fails_{$ip}");

        try {
            LoginAttemptLog::create([
                'ip_address' => $ip,
                'email'      => $email,
                'user_agent' => $request->userAgent(),
                'location'   => self::resolveLocation($ip),
                'is_success' => true,
            ]);
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    /**
     * Send email alert to system setting email with 1-click block link.
     */
    protected function sendSecurityAlertEmail(string $ip, ?string $email, string $location, ?string $userAgent, int $attempts): void
    {
        // Read destination email from System Settings (fallback to config)
        $targetEmail = AppSetting::get('email') ?: config('mail.from.address');
        if (empty($targetEmail)) {
            $targetEmail = 'ebsbazarbd@gmail.com';
        }

        // Generate secure 1-click block URL valid for 7 days
        $blockUrl = URL::temporarySignedRoute(
            'security.ip-block.quick-block',
            now()->addDays(7),
            ['ip' => $ip]
        );

        $timestamp = now()->timezone('Asia/Dhaka')->format('d M Y, h:i A');

        try {
            Mail::to($targetEmail)->send(new AdminFailedLoginAlertMail(
                ip: $ip,
                attemptedEmail: $email,
                location: $location,
                userAgent: $userAgent,
                attempts: $attempts,
                blockUrl: $blockUrl,
                timestamp: $timestamp
            ));

            Log::info("Security alert email sent to {$targetEmail} for IP {$ip}");
        } catch (\Throwable $e) {
            Log::error("Failed to send security alert email to {$targetEmail}: " . $e->getMessage());
        }
    }

    /**
     * Block an IP address.
     */
    public static function blockIp(
        string $ip,
        ?string $reason = null,
        ?int $userId = null,
        ?string $userAgent = null,
        ?string $attemptedEmail = null
    ): BlockedIp {
        $location = self::resolveLocation($ip);

        $record = BlockedIp::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason'          => $reason ?: 'Blocked by Administrator',
                'location'        => $location,
                'user_agent'      => $userAgent,
                'attempted_email' => $attemptedEmail,
                'is_active'       => true,
                'blocked_by'      => $userId,
                'blocked_at'      => now(),
            ]
        );

        Cache::forget("blocked_ip_check_{$ip}");
        Cache::forget('global_blocked_ips_list');

        return $record;
    }

    /**
     * Unblock an IP address.
     */
    public static function unblockIp(string $ip): void
    {
        BlockedIp::where('ip_address', $ip)->update(['is_active' => false]);
        Cache::forget("blocked_ip_check_{$ip}");
        Cache::forget('global_blocked_ips_list');
    }

    /**
     * Check if an IP address is currently blocked.
     */
    public static function isBlocked(string $ip): bool
    {
        return Cache::remember("blocked_ip_check_{$ip}", 60, function () use ($ip) {
            return BlockedIp::where('ip_address', $ip)->where('is_active', true)->exists();
        });
    }
}

