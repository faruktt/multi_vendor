<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class BranchAccess
{
    public function handle(Request $request, Closure $next)
    {
        $branch = $request->route('branch');
        $user   = auth()->user();

        // So route('admin.warehouse.xxx') / route('branch.xxx', ...) calls in views don't
        // need to repeat the branch param — it's filled in from whichever branch URL brought us here.
        if ($branch) {
            URL::defaults(['branch' => $branch->is_warehouse ? 'warehouse' : $branch->getRouteKey()]);
        }

        if ($user->hasRole('super-admin')) {
            view()->share('branch', $branch);
            return $next($request);
        }

        if (!$branch || $user->vendor_id != $branch->id) {
            abort(403, 'You do not have access to this branch.');
        }

        view()->share('branch', $branch);
        return $next($request);
    }
}
