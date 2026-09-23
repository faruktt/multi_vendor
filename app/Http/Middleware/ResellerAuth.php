<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ResellerAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth('reseller')->check()) {
            return redirect()->route('reseller.login')->with('error', 'Please login to continue.');
        }

        $reseller = auth('reseller')->user();

        if (!$reseller->isActive()) {
            auth('reseller')->logout();
            return redirect()->route('reseller.login')->with('error',
                $reseller->isPending()
                    ? 'Your account is pending admin approval.'
                    : 'Your account has been deactivated.'
            );
        }

        // Share reseller info with all views inside reseller routes
        view()->share('reseller', $reseller);

        return $next($request);
    }
}
