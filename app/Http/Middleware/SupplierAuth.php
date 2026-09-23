<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SupplierAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth('supplier')->check()) {
            return redirect()->route('supplier.login')->with('error', 'Please login to continue.');
        }

        $supplier = auth('supplier')->user();

        if (!$supplier->isActive()) {
            auth('supplier')->logout();
            return redirect()->route('supplier.login')->with('error',
                $supplier->isPending()
                    ? 'Your account is pending admin approval.'
                    : 'Your account has been deactivated.'
            );
        }

        // Share supplier info with all views inside supplier routes
        view()->share('supplier', $supplier);

        return $next($request);
    }
}
