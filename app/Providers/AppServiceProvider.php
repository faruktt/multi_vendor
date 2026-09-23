<?php

namespace App\Providers;

use App\Helpers\AppSetting;
use App\Models\Category;
use App\Models\Vendor;
use App\Support\Cart;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Authenticate::redirectUsing(fn(Request $request) => null);

        // Register Activity Observer for audit logging
        $observedModels = [
            \App\Models\Product::class,
            \App\Models\ProductVariant::class,
            \App\Models\Category::class,
            \App\Models\Sale::class,
            \App\Models\Customer::class,
            \App\Models\Supplier::class,
            \App\Models\Purchase::class,
            \App\Models\User::class,
            \App\Models\Coupon::class,
            \App\Models\Banner::class,
            \App\Models\IncomeExpense::class,
            \App\Models\CourierSetting::class,
            \App\Models\OrderStatus::class,
        ];

        foreach ($observedModels as $modelClass) {
            $modelClass::observe(\App\Observers\ActivityObserver::class);
        }

        // {branch} route param → Vendor model
        Route::model('branch', Vendor::class);

        // Inject shared data into every view
        view()->composer('*', function ($view) {
            // App-wide settings always available (reads storage/app/settings.json)
            $view->with('appSettings', AppSetting::all());

            if (auth()->check()) {
                $view->with('branches', Vendor::orderBy('name')->get());
                // $branch is set by BranchAccess middleware; fallback for admin/non-branch pages
                if (!isset($view->getData()['branch'])) {
                    $view->with('branch', request()->route('branch'));
                }
            }
        });

        // Public storefront: always share the cart contents and category list for the header/drawer
        view()->composer('shop.*', function ($view) {
            $branch = $view->getData()['branch'] ?? request()->route('branch');
            if ($branch instanceof Vendor) {
                $cartLines = Cart::lines($branch);
                $view->with('cartLines', $cartLines);
                $view->with('cartCount', $cartLines->count());
                $view->with('cartSubtotal', $cartLines->sum('subtotal'));

                if (!isset($view->getData()['categories'])) {
                    $view->with('categories', Category::withoutGlobalScopes()
                        ->where('vendor_id', $branch->id)
                        ->whereNull('parent_id')
                        ->with(['children' => fn($q) => $q->withoutGlobalScopes()->orderBy('name')])
                        ->orderBy('name')
                        ->get());
                }
            }
        });

        // Reseller portal: always share the cart count for the header/sidebar
        view()->composer('reseller.*', function ($view) {
            $view->with('cartCount', \App\Http\Controllers\Reseller\CartController::count());
        });
    }
}
