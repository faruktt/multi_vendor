<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAccess
{
    /**
     * Handle an incoming request for admin routes.
     * Super Admin always has full access.
     * If a permission parameter is specified (e.g. 'manage_users' or 'view_sales|create_sales'),
     * user must have at least one of those permissions.
     */
    public function handle(Request $request, Closure $next, ?string $permission = null)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin has unrestricted access to everything
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // If a specific permission is required for this route/section
        if ($permission) {
            $perms = explode('|', $permission);
            if ($user->hasAnyPermission($perms)) {
                return $next($request);
            }
            abort(403, 'You do not have permission to access this administrative resource.');
        }

        // Generic admin panel entry check
        if ($user->hasAnyRole(['sub-admin', 'manager', 'admin', 'moderator']) || $user->getAllPermissions()->isNotEmpty()) {
            return $next($request);
        }

        abort(403, 'Unauthorized administrative access.');
    }
}
