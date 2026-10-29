<?php

namespace App\Http\Middleware;

use App\Services\Security\IpSecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedIp
{
    public function handle(Request $request, Closure $next): Response
    {
        // Don't block the quick-block route or unblock actions
        if ($request->routeIs('security.ip-block.*')) {
            return $next($request);
        }

        $ip = $request->ip();

        if (IpSecurityService::isBlocked($ip)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => "Your IP address ({$ip}) has been blocked by security administrator.",
                ], 403);
            }

            return response()->view('errors.ip-blocked', ['ip' => $ip], 403);
        }

        return $next($request);
    }
}

