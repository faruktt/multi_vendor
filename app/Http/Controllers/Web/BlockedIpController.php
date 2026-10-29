<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use App\Models\LoginAttemptLog;
use App\Services\Security\IpSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlockedIpController extends Controller
{
    /**
     * 1-Click quick block endpoint invoked from security alert email (signed URL).
     */
    public function quickBlock(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired signature link. Please log into the Admin Panel to manage blocked IPs.');
        }

        $ip = trim($request->query('ip', ''));
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            abort(400, 'Invalid IP address specified.');
        }

        // Block IP immediately
        IpSecurityService::blockIp(
            ip: $ip,
            reason: 'Blocked via 1-Click Email Alert',
            userAgent: $request->userAgent()
        );

        $location = IpSecurityService::resolveLocation($ip);

        return view('security.blocked-confirmation', [
            'ip'       => $ip,
            'location' => $location,
        ]);
    }

    /**
     * Admin view for managing blocked IPs and viewing security logs.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $statusFilter = $request->input('status', 'all');

        $query = BlockedIp::with('blockedBy');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('attempted_email', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $blockedIps = $query->latest('id')->paginate(15)->withQueryString();

        // Recent login attempt logs
        $recentAttempts = LoginAttemptLog::latest('id')->take(25)->get();

        // Metrics
        $totalBlocked  = BlockedIp::count();
        $activeBlocked = BlockedIp::where('is_active', true)->count();
        $recentFails   = LoginAttemptLog::where('is_success', false)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        // Also fetch active blocked IPs list as array to easily highlight in attempts log
        $activeBlockedIpAddresses = BlockedIp::where('is_active', true)->pluck('ip_address')->toArray();

        return view('admin.ip-blocks.index', compact(
            'blockedIps',
            'recentAttempts',
            'totalBlocked',
            'activeBlocked',
            'recentFails',
            'search',
            'statusFilter',
            'activeBlockedIpAddresses'
        ));
    }

    /**
     * Manually block an IP address from Admin Panel.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|string|max:45',
            'reason'     => 'nullable|string|max:255',
        ]);

        $ip = trim($validated['ip_address']);

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return back()->with('error', 'Invalid IP address format. Please enter a valid IPv4 or IPv6 address.');
        }

        // Prevent self-locking if user's current IP matches
        if ($ip === $request->ip() && !$request->has('force_self')) {
            return back()->with('error', "Warning: The IP {$ip} matches your current connection IP. Self-blocking is prevented to avoid lockout.");
        }

        IpSecurityService::blockIp(
            ip: $ip,
            reason: $validated['reason'] ?: 'Manually blocked by Administrator',
            userId: auth()->id()
        );

        return back()->with('success', "IP address {$ip} has been successfully blocked.");
    }

    /**
     * Unblock an IP address.
     */
    public function unblock(BlockedIp $blockedIp)
    {
        $blockedIp->update(['is_active' => false]);
        Cache::forget("blocked_ip_check_{$blockedIp->ip_address}");
        Cache::forget('global_blocked_ips_list');

        return back()->with('success', "IP {$blockedIp->ip_address} unblocked successfully.");
    }

    /**
     * Re-block an IP address.
     */
    public function block(BlockedIp $blockedIp)
    {
        $blockedIp->update([
            'is_active'  => true,
            'blocked_at' => now(),
            'blocked_by' => auth()->id(),
        ]);
        Cache::forget("blocked_ip_check_{$blockedIp->ip_address}");
        Cache::forget('global_blocked_ips_list');

        return back()->with('success', "IP {$blockedIp->ip_address} blocked successfully.");
    }

    /**
     * Delete an IP from the block records entirely.
     */
    public function destroy(BlockedIp $blockedIp)
    {
        $ip = $blockedIp->ip_address;
        $blockedIp->delete();
        Cache::forget("blocked_ip_check_{$ip}");
        Cache::forget('global_blocked_ips_list');

        return back()->with('success', "IP {$ip} removed from block records.");
    }
}

