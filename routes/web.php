<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\SaleController;
use App\Http\Controllers\Web\POSController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\StockController;
use App\Http\Controllers\Web\FinanceController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\FraudCheckController;
use App\Http\Controllers\Web\GlobalSettingsController;
use App\Http\Controllers\Web\AdminDataController;
use App\Http\Controllers\Web\RolePermissionController;
use App\Http\Controllers\Web\OrderStatusController;
use App\Http\Controllers\Web\PaymentMethodController;
use App\Http\Controllers\Web\SaleReturnController;
use App\Http\Controllers\Web\BannerController;
use App\Http\Controllers\Web\ActivityLogController;
use App\Http\Controllers\Web\UserActivityController;
use App\Http\Controllers\Web\FlashSaleController;
use App\Http\Controllers\Web\HomeContentController;
use App\Http\Controllers\Web\AdsCostController;
use App\Http\Controllers\Web\EpbxCallController;
use App\Http\Controllers\Warehouse\DashboardController as WarehouseDashboardController;
use App\Http\Controllers\Warehouse\ProductController as WarehouseProductController;
use App\Http\Controllers\Warehouse\CategoryController as WarehouseCategoryController;
use App\Http\Controllers\Warehouse\PurchaseController as WarehousePurchaseController;
use App\Http\Controllers\Warehouse\PurchaseReturnController as WarehousePurchaseReturnController;
use App\Http\Controllers\Warehouse\SupplierController as WarehouseSupplierController;
use App\Http\Controllers\Warehouse\StockController as WarehouseStockController;
use App\Http\Controllers\Warehouse\StaffController as WarehouseStaffController;
use App\Http\Controllers\Warehouse\SettingsController as WarehouseSettingsController;
use App\Http\Controllers\Warehouse\TransferController as WarehouseTransferController;
use App\Http\Controllers\Warehouse\VariantAttributeController as WarehouseVariantAttributeController;
use App\Http\Controllers\Shop\HomeController as ShopHomeController;
use App\Http\Controllers\Shop\ProductController as ShopProductController;
use App\Http\Controllers\Shop\CartController as ShopCartController;
use App\Http\Controllers\Shop\CheckoutController as ShopCheckoutController;
use App\Http\Controllers\Shop\OrderTrackingController as ShopOrderTrackingController;
use App\Http\Controllers\Shop\CustomerAuthController;
use App\Http\Controllers\Shop\CustomerAccountController;
use App\Http\Controllers\Shop\ChatController as ShopChatController;
use App\Http\Controllers\Web\AdminChatController;
use App\Http\Controllers\Reseller\AuthController as ResellerAuthController;
use App\Http\Controllers\Reseller\DashboardController as ResellerDashboardController;
use App\Http\Controllers\Reseller\ProductController as ResellerProductController;
use App\Http\Controllers\Reseller\CartController as ResellerCartController;
use App\Http\Controllers\Reseller\OrderController as ResellerOrderController;
use App\Http\Controllers\Reseller\WithdrawalController as ResellerWithdrawalController;
use App\Http\Controllers\Reseller\AccountController as ResellerAccountController;
use App\Http\Controllers\Reseller\ProfileController as ResellerProfileController;
use App\Http\Controllers\Web\ResellerManagementController;
use App\Http\Controllers\Web\CouponController;
use App\Http\Controllers\Web\ShippingChargeController;
use App\Http\Controllers\Web\Admin\CourierSettingController;
use App\Http\Controllers\Web\CourierOrderController;
use App\Http\Controllers\Moderator\ModeratorAuthController;
use App\Http\Controllers\Moderator\ModeratorDashboardController;
use App\Http\Controllers\Moderator\ModeratorWorkController;
use App\Http\Controllers\Moderator\ModeratorProfileController;
use App\Http\Controllers\Moderator\ModeratorAccountController;
use App\Http\Controllers\Web\Admin\ModeratorManagementController;
use App\Http\Controllers\Supplier\AuthController as SupplierAuthController;
use App\Http\Controllers\Supplier\DashboardController as SupplierDashboardController;
use App\Http\Controllers\Supplier\ProductController as SupplierProductController;
use App\Http\Controllers\Supplier\OrderController as SupplierOrderController;
use App\Http\Controllers\Supplier\ProfileController as SupplierProfileController;
use App\Http\Controllers\Supplier\WithdrawalController as SupplierWithdrawalController;
use App\Http\Controllers\Web\SupplierManagementController;
use App\Http\Controllers\Web\SupplierProductManagementController;

// ── Public storefront (guest, no auth) — lives at the main domain root ──────
Route::get('/', [ShopHomeController::class, 'index'])->name('root');
Route::get('/product/{productSlug}', [ShopProductController::class, 'show'])->name('shop.products.show');
Route::get('/supplier-store/{supplier}', [ShopProductController::class, 'supplierStore'])->name('shop.supplier.show');

// ── Customer Auth & Account Portal ──────────────────────────────────────────
Route::prefix('customer')->name('shop.customer.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login',     [CustomerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login',    [CustomerAuthController::class, 'login'])->name('login.submit');
        Route::get('/register',  [CustomerAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [CustomerAuthController::class, 'register'])->name('register.submit');
    });

    Route::middleware('auth:customer')->group(function () {
        Route::post('/logout',          [CustomerAuthController::class, 'logout'])->name('logout');
        Route::get('/account',          [CustomerAccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders',           [CustomerAccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{id}',      [CustomerAccountController::class, 'orderDetail'])->name('orders.show');
        Route::get('/profile',          [CustomerAccountController::class, 'profile'])->name('profile');
        Route::put('/profile',          [CustomerAccountController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [CustomerAccountController::class, 'updatePassword'])->name('password.update');

        // Customer Live Chat
        Route::prefix('chat')->name('chat.')->group(function () {
            Route::post('/init', [ShopChatController::class, 'init'])->name('init');
            Route::post('/send', [ShopChatController::class, 'sendMessage'])->name('send');
            Route::get('/poll',  [ShopChatController::class, 'poll'])->name('poll');
        });
    });
});

Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/',                          [ShopHomeController::class, 'index'])->name('home');
    Route::get('/products',                    [ShopProductController::class, 'index'])->name('products.index');

    Route::post('/cart/add',                 [ShopCartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update',               [ShopCartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{key}',       [ShopCartController::class, 'remove'])->name('cart.remove');

    Route::get('/checkout',                  [ShopCheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout',                 [ShopCheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout/coupon',          [ShopCheckoutController::class, 'applyCoupon'])->name('checkout.coupon');
    Route::get('/order/{saleId}/confirmation', [ShopCheckoutController::class, 'success'])->name('checkout.success');

    Route::get('/track',                     [ShopOrderTrackingController::class, 'index'])->name('track.index');

    // Category pages use short URLs (/shop/{categorySlug}) — must stay LAST in this group:
    // it's a single-segment catch-all and would otherwise swallow the static routes above.
    Route::get('/{categorySlug}',            [ShopProductController::class, 'index'])->name('products.category');
});

// ── Reseller Portal ──────────────────────────────────────────────────────────
Route::prefix('reseller')->name('reseller.')->group(function () {

    // Public (guest) auth routes
    Route::get('/login',     [ResellerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [ResellerAuthController::class, 'login'])->name('login.submit');
    Route::get('/register',  [ResellerAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [ResellerAuthController::class, 'register'])->name('register.submit');

    // Authenticated reseller routes
    Route::middleware('reseller.auth')->group(function () {
        Route::post('/logout', [ResellerAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [ResellerDashboardController::class, 'index'])->name('dashboard');

        Route::get('/products',      [ResellerProductController::class, 'index'])->name('products.index');
        Route::get('/products/{id}', [ResellerProductController::class, 'show'])->name('products.show');

        Route::get('/cart',          [ResellerCartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add',     [ResellerCartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update',  [ResellerCartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/{key}', [ResellerCartController::class, 'remove'])->name('cart.remove');

        Route::get('/orders',        [ResellerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [ResellerOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders',       [ResellerOrderController::class, 'store'])->name('orders.store');
        Route::post('/orders/coupon',[ResellerOrderController::class, 'applyCoupon'])->name('orders.coupon');
        Route::get('/orders/{id}',   [ResellerOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{id}/invoice', [ResellerOrderController::class, 'invoice'])->name('orders.invoice');

        Route::get('/withdrawals',   [ResellerWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals',  [ResellerWithdrawalController::class, 'store'])->name('withdrawals.store');

        // My Account & Financial Overview
        Route::get('/account', [ResellerAccountController::class, 'index'])->name('account');

        // Profile Settings & Photo Upload
        Route::get('/profile', [ResellerProfileController::class, 'index'])->name('profile');
        Route::post('/profile', [ResellerProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [ResellerProfileController::class, 'changePassword'])->name('password.update');
    });
});

// ── Moderator Portal ────────────────────────────────────────────────────────
Route::prefix('moderator')->name('moderator.')->group(function () {
    Route::get('/login',  [ModeratorAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [ModeratorAuthController::class, 'login'])->name('login.submit');

    Route::middleware('moderator.auth')->group(function () {
        Route::post('/logout', [ModeratorAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [ModeratorDashboardController::class, 'index'])->name('dashboard');

        // Work Session Tracking & Reporting
        Route::post('/work/start',            [ModeratorWorkController::class, 'startWork'])->name('work.start');
        Route::post('/work/stop',             [ModeratorWorkController::class, 'stopWork'])->name('work.stop');
        Route::post('/work/end',              [ModeratorWorkController::class, 'endWork'])->name('work.end');
        Route::post('/work/log',              [ModeratorWorkController::class, 'logActivity'])->name('work.log');
        Route::delete('/work/log/{log}',      [ModeratorWorkController::class, 'deleteActivityLog'])->name('work.log.delete');
        Route::get('/reports',                [ModeratorWorkController::class, 'myReports'])->name('reports');
        Route::post('/work/{session}/report', [ModeratorWorkController::class, 'updateReport'])->name('work.report.update');

        // My Account (কাজের হিসাব ও বেতন উত্তোলন)
        Route::get('/account',                [ModeratorAccountController::class, 'index'])->name('account');
        Route::post('/withdrawals',           [ModeratorAccountController::class, 'requestWithdrawal'])->name('withdrawals.store');

        // Profile & Settings
        Route::get('/profile',                [ModeratorProfileController::class, 'index'])->name('profile');
        Route::post('/profile',               [ModeratorProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password',      [ModeratorProfileController::class, 'changePassword'])->name('password.update');
    });
});

// ── Supplier / Vendor Portal ──────────────────────────────────────────────────
Route::prefix('supplier')->name('supplier.')->group(function () {

    // Public (guest) auth routes
    Route::get('/login',     [SupplierAuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [SupplierAuthController::class, 'login'])->name('login.submit');
    Route::get('/register',  [SupplierAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [SupplierAuthController::class, 'register'])->name('register.submit');

    // Authenticated supplier routes
    Route::middleware('supplier.auth')->group(function () {
        Route::post('/logout', [SupplierAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [SupplierDashboardController::class, 'index'])->name('dashboard');

        // Products
        Route::get('/products',                    [SupplierProductController::class, 'index'])->name('products.index');
        Route::get('/products/create',             [SupplierProductController::class, 'create'])->name('products.create');
        Route::post('/products',                   [SupplierProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit',     [SupplierProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}',          [SupplierProductController::class, 'update'])->name('products.update');
        Route::post('/products/{product}/toggle',  [SupplierProductController::class, 'toggleStatus'])->name('products.toggle');
        Route::delete('/products/{product}',       [SupplierProductController::class, 'destroy'])->name('products.destroy');

        // Orders / Sales
        Route::get('/orders',                      [SupplierOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}',                 [SupplierOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{id}/invoice',         [SupplierOrderController::class, 'invoice'])->name('orders.invoice');

        // Store Profile & Settings
        Route::get('/profile',                     [SupplierProfileController::class, 'index'])->name('profile');
        Route::post('/profile',                    [SupplierProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password',           [SupplierProfileController::class, 'changePassword'])->name('password.update');

        // Withdrawals & Financial Payouts
        Route::get('/withdrawals',                 [SupplierWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals',                [SupplierWithdrawalController::class, 'store'])->name('withdrawals.store');
    });
});

// Optional numeric fallback for supplier store /supplier/{id}
Route::get('/supplier/{supplier}', [ShopProductController::class, 'supplierStore'])->whereNumber('supplier');

// ── Internal system (login, admin panel, all branch operations) — under /admin ──
Route::prefix('admin')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login',    [AuthController::class, 'login']);
        Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register']);
    });

    Route::middleware('auth')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Root of /admin → redirect based on role
        Route::get('/', function () {
            $user = auth()->user();
            if ($user->hasRole('super-admin')) {
                return redirect()->route('admin.index');
            }
            return redirect()->route('branch.dashboard', $user->vendor_id);
        });

        // 096XX Cloud PBX & IP Telephony
        Route::post('/sales/{sale}/epbx-verify-call', [EpbxCallController::class, 'makeVerificationCall'])->name('epbx.verify-call');
        Route::post('/sales/{sale}/epbx-call-note',   [EpbxCallController::class, 'saveCallNote'])->name('epbx.call-note');

        // ── Super-admin panel ──────────────────────────────────────────────
        Route::middleware('role:super-admin')->name('admin.')->group(function () {
            Route::get('/', [AdminController::class, 'index'])->name('index');

            // Global system settings
            Route::get('/settings',  [GlobalSettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings', [GlobalSettingsController::class, 'update'])->name('settings.update');
            Route::post('/settings/profile',  [GlobalSettingsController::class, 'updateProfile'])->name('settings.profile');
            Route::post('/settings/password', [GlobalSettingsController::class, 'changePassword'])->name('settings.password');

            // Branch (vendor) management
            Route::post('/branches',              [AdminController::class, 'storeBranch'])->name('branches.store');
            Route::put('/branches/{branch}',      [AdminController::class, 'updateBranch'])->name('branches.update');
            Route::delete('/branches/{branch}',   [AdminController::class, 'destroyBranch'])->name('branches.destroy');

            // Staff management across all branches
            Route::get('/staff',             [UserController::class, 'adminIndex'])->name('staff.index');
            Route::post('/staff',            [UserController::class, 'adminStore'])->name('staff.store');
            Route::put('/staff/{user}',      [UserController::class, 'adminUpdate'])->name('staff.update');
            Route::delete('/staff/{user}',   [UserController::class, 'adminDestroy'])->name('staff.destroy');
            Route::get('/staff/{user}/commission', [UserController::class, 'commission'])->name('staff.commission');

            Route::get('/all-sales',                 [AdminDataController::class, 'allSales'])->name('all-sales');
            Route::post('/all-sales/{sale}/status',   [AdminDataController::class, 'updateStatus'])->name('all-sales.status');
            Route::get('/all-sales/bulk-print',       [SaleController::class, 'bulkPrint'])->name('all-sales.bulk-print');
            Route::post('/all-sales/bd-courier-check', [AdminDataController::class, 'bdCourierCheck'])->name('bd-courier-check');
            Route::get('/all-sales/{sale}/notes',    [AdminDataController::class, 'getNotes'])->name('all-sales.notes.index');
            Route::post('/all-sales/{sale}/notes',   [AdminDataController::class, 'addNote'])->name('all-sales.notes.store');
            Route::get('/all-products',   [AdminDataController::class, 'allProducts'])->name('all-products');
            Route::get('/all-categories', [AdminDataController::class, 'allCategories'])->name('all-categories');
            Route::get('/all-purchases',  [AdminDataController::class, 'allPurchases'])->name('all-purchases');
            Route::get('/all-suppliers',  [AdminDataController::class, 'allSuppliers'])->name('all-suppliers');
            Route::get('/stock-report',     [AdminDataController::class, 'stockReport'])->name('stock-report');
            Route::get('/fraud-check',      [AdminDataController::class, 'fraudCheck'])->name('fraud-check');
            Route::get('/all-customers',    [AdminDataController::class, 'allCustomers'])->name('all-customers');
            Route::get('/sales-report',     [AdminDataController::class, 'salesReport'])->name('sales-report');
            Route::get('/financial-report', [AdminDataController::class, 'financialReport'])->name('financial-report');

            // ── Ads Cost Management ───────────────────────────────────────
            Route::prefix('ads-cost')->name('ads-cost.')->group(function () {
                Route::get('/',             [AdsCostController::class, 'index'])->name('index');
                Route::post('/',            [AdsCostController::class, 'store'])->name('store');
                Route::put('/{adsCost}',    [AdsCostController::class, 'update'])->name('update');
                Route::delete('/{adsCost}', [AdsCostController::class, 'destroy'])->name('destroy');
                Route::get('/export',       [AdsCostController::class, 'export'])->name('export');
            });

            // Activity Logs
            Route::get('/activity-logs',                 [ActivityLogController::class, 'index'])->name('activity-logs.index');
            Route::get('/activity-logs/{activityLog}',    [ActivityLogController::class, 'show'])->name('activity-logs.show');
            Route::delete('/activity-logs/clear',        [ActivityLogController::class, 'clear'])->name('activity-logs.clear');

            // User Activity (Traffic & Page Hits Analytics)
            Route::get('/user-activity',                 [UserActivityController::class, 'index'])->name('user-activity.index');
            Route::delete('/user-activity/clear',        [UserActivityController::class, 'clear'])->name('user-activity.clear');

            // Role & Permission management
            Route::get('/roles-permissions',  [RolePermissionController::class, 'index'])->name('roles-permissions');
            Route::post('/roles-permissions', [RolePermissionController::class, 'update'])->name('roles-permissions.update');

            // Order status options (used on the Sales pages)
            Route::get('/order-statuses',                  [OrderStatusController::class, 'index'])->name('order-statuses.index');
            Route::post('/order-statuses',                 [OrderStatusController::class, 'store'])->name('order-statuses.store');
            Route::put('/order-statuses/{orderStatus}',     [OrderStatusController::class, 'update'])->name('order-statuses.update');
            Route::post('/order-statuses/reorder',          [OrderStatusController::class, 'reorder'])->name('order-statuses.reorder');
            Route::delete('/order-statuses/{orderStatus}',  [OrderStatusController::class, 'destroy'])->name('order-statuses.destroy');

            // Payment methods (used on POS, Sales, Purchases, and refund forms across the app)
            Route::get('/payment-methods',                        [PaymentMethodController::class, 'index'])->name('payment-methods.index');
            Route::post('/payment-methods',                       [PaymentMethodController::class, 'store'])->name('payment-methods.store');
            Route::put('/payment-methods/{paymentMethod}',        [PaymentMethodController::class, 'update'])->name('payment-methods.update');
            Route::post('/payment-methods/{paymentMethod}/toggle', [PaymentMethodController::class, 'toggleActive'])->name('payment-methods.toggle');
            Route::post('/payment-methods/reorder',               [PaymentMethodController::class, 'reorder'])->name('payment-methods.reorder');
            Route::delete('/payment-methods/{paymentMethod}',     [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');

            // Homepage banners (global — the storefront has a single online-store branch)
            Route::get('/banners',              [BannerController::class, 'index'])->name('banners.index');
            Route::post('/banners',             [BannerController::class, 'store'])->name('banners.store');
            Route::post('/banners/reorder',     [BannerController::class, 'reorder'])->name('banners.reorder');
            Route::put('/banners/{banner}',     [BannerController::class, 'update'])->name('banners.update');
            Route::post('/banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');
            Route::delete('/banners/{banner}',  [BannerController::class, 'destroy'])->name('banners.destroy');

            // ── Coupons Management ───────────────────────────────────────
            Route::prefix('coupons')->name('coupons.')->group(function () {
                Route::get('/',                 [CouponController::class, 'index'])->name('index');
                Route::post('/',                [CouponController::class, 'store'])->name('store');
                Route::put('/{coupon}',         [CouponController::class, 'update'])->name('update');
                Route::post('/{coupon}/toggle', [CouponController::class, 'toggleStatus'])->name('toggle');
                Route::delete('/{coupon}',      [CouponController::class, 'destroy'])->name('destroy');
            });

            // ── Flash Sales Management ───────────────────────────────────
            Route::prefix('flash-sales')->name('flash-sales.')->group(function () {
                Route::get('/',                    [FlashSaleController::class, 'index'])->name('index');
                Route::post('/',                   [FlashSaleController::class, 'store'])->name('store');
                Route::put('/{flashSale}',         [FlashSaleController::class, 'update'])->name('update');
                Route::post('/{flashSale}/toggle', [FlashSaleController::class, 'toggleStatus'])->name('toggle');
                Route::delete('/{flashSale}',      [FlashSaleController::class, 'destroy'])->name('destroy');
            });

            // ── Homepage Content Management ──────────────────────────────
            Route::prefix('home-contents')->name('home-contents.')->group(function () {
                Route::get('/',                        [HomeContentController::class, 'index'])->name('index');
                Route::post('/',                       [HomeContentController::class, 'store'])->name('store');
                Route::put('/{homeContent}',           [HomeContentController::class, 'update'])->name('update');
                Route::post('/{homeContent}/toggle',   [HomeContentController::class, 'toggle'])->name('toggle');
                Route::delete('/{homeContent}',        [HomeContentController::class, 'destroy'])->name('destroy');
            });

            // ── Shipping Charges Management ──────────────────────────────
            Route::prefix('shipping-charges')->name('shipping-charges.')->group(function () {
                Route::get('/',  [ShippingChargeController::class, 'index'])->name('index');
                Route::post('/', [ShippingChargeController::class, 'update'])->name('update');
            });

            // ── Reseller Management ──────────────────────────────────────
            Route::prefix('resellers')->name('resellers.')->group(function () {
                Route::get('/',                    [ResellerManagementController::class, 'index'])->name('index');
                Route::post('/{reseller}/approve', [ResellerManagementController::class, 'approve'])->name('approve');
                Route::post('/{reseller}/reject',  [ResellerManagementController::class, 'reject'])->name('reject');
                Route::delete('/{reseller}',       [ResellerManagementController::class, 'destroy'])->name('destroy');
                Route::post('/{reseller}/withdraw',[ResellerManagementController::class, 'withdrawProfit'])->name('withdraw');
                Route::get('/orders',              [ResellerManagementController::class, 'orders'])->name('orders');
                Route::get('/report',              [ResellerManagementController::class, 'report'])->name('report');
                Route::get('/withdrawals',                       [ResellerManagementController::class, 'withdrawals'])->name('withdrawals');
                Route::post('/withdrawals/{withdrawal}/approve', [ResellerManagementController::class, 'approveWithdrawal'])->name('withdrawals.approve');
                Route::post('/withdrawals/{withdrawal}/reject',  [ResellerManagementController::class, 'rejectWithdrawal'])->name('withdrawals.reject');
            });

            // ── Supplier / Vendor Management ─────────────────────────────
            Route::prefix('suppliers-management')->name('suppliers.')->group(function () {
                Route::get('/',                            [SupplierManagementController::class, 'index'])->name('manage');
                Route::post('/{supplier}/approve',         [SupplierManagementController::class, 'approve'])->name('approve');
                Route::post('/{supplier}/reject',          [SupplierManagementController::class, 'reject'])->name('reject');
                Route::delete('/{supplier}',               [SupplierManagementController::class, 'destroy'])->name('destroy');
                Route::post('/{supplier}/commission',      [SupplierManagementController::class, 'updateCommission'])->name('commission.update');
                Route::get('/{supplier}/commission-report',[SupplierManagementController::class, 'commissionReport'])->name('commission.report');
                Route::get('/withdrawals',                       [SupplierManagementController::class, 'withdrawals'])->name('withdrawals');
                Route::post('/withdrawals/{withdrawal}/approve', [SupplierManagementController::class, 'approveWithdrawal'])->name('withdrawals.approve');
                Route::post('/withdrawals/{withdrawal}/reject',  [SupplierManagementController::class, 'rejectWithdrawal'])->name('withdrawals.reject');
            });

            // ── Supplier Sales / Marketplace Orders ──────────────────────
            Route::prefix('supplier-sales')->name('supplier-sales.')->group(function () {
                Route::get('/',              [\App\Http\Controllers\Web\SupplierSaleController::class, 'index'])->name('index');
                Route::get('/{sale}',        [\App\Http\Controllers\Web\SupplierSaleController::class, 'show'])->name('show');
                Route::post('/{sale}/status',[\App\Http\Controllers\Web\SupplierSaleController::class, 'updateStatus'])->name('status');
                Route::get('/{sale}/invoice',[\App\Http\Controllers\Web\SupplierSaleController::class, 'invoice'])->name('invoice');
            });

            // ── Supplier Products Management (Approval & Commission) ──────
            Route::prefix('supplier-products')->name('supplier-products.')->group(function () {
                Route::get('/',                          [SupplierProductManagementController::class, 'index'])->name('index');
                Route::post('/{product}/approve',        [SupplierProductManagementController::class, 'approve'])->name('approve');
                Route::post('/{product}/reject',         [SupplierProductManagementController::class, 'reject'])->name('reject');
                Route::post('/{product}/commission',     [SupplierProductManagementController::class, 'updateCommission'])->name('commission.update');
            });

            // ── Live Chat / Messages ─────────────────────────────────────
            Route::prefix('messages')->name('messages.')->group(function () {
                Route::get('/',                     [AdminChatController::class, 'index'])->name('index');
                Route::get('/conversations',        [AdminChatController::class, 'conversations'])->name('conversations');
                Route::get('/unread-count',         [AdminChatController::class, 'unreadCount'])->name('unread-count');
                Route::get('/{conversation}',       [AdminChatController::class, 'messages'])->name('show');
                Route::post('/{conversation}/send', [AdminChatController::class, 'sendMessage'])->name('send');
                Route::get('/{conversation}/poll',  [AdminChatController::class, 'poll'])->name('poll');
            });

            // ── Courier Settings Management ──────────────────────────────
            Route::prefix('couriers')->name('couriers.')->group(function () {
                Route::get('/',                  [CourierSettingController::class, 'index'])->name('index');
                Route::post('/{courier}',        [CourierSettingController::class, 'update'])->name('update');
                Route::post('/{courier}/toggle', [CourierSettingController::class, 'toggle'])->name('toggle');
                Route::post('/{courier}/test',   [CourierSettingController::class, 'test'])->name('test');
            });

            // ── Moderator Management & Reports ───────────────────────────
            Route::prefix('moderators')->name('moderators.')->group(function () {
                Route::get('/',                                  [ModeratorManagementController::class, 'index'])->name('index');
                Route::post('/',                                 [ModeratorManagementController::class, 'store'])->name('store');
                Route::put('/{moderator}',                       [ModeratorManagementController::class, 'update'])->name('update');
                Route::post('/{moderator}/toggle',               [ModeratorManagementController::class, 'toggleStatus'])->name('toggle');
                Route::delete('/{moderator}',                    [ModeratorManagementController::class, 'destroy'])->name('destroy');
                Route::get('/reports',                           [ModeratorManagementController::class, 'reports'])->name('reports');
                Route::get('/withdrawals',                       [ModeratorManagementController::class, 'withdrawals'])->name('withdrawals');
                Route::post('/withdrawals/{withdrawal}/approve', [ModeratorManagementController::class, 'approveWithdrawal'])->name('withdrawals.approve');
                Route::post('/withdrawals/{withdrawal}/reject',  [ModeratorManagementController::class, 'rejectWithdrawal'])->name('withdrawals.reject');
            });
        });

        // ── Courier Order Dispatch (Global) ───────────────────────────────────
        Route::post('/courier/orders/{sale}/send',   [CourierOrderController::class, 'sendToCourier'])->name('courier.orders.send');
        Route::get('/courier/orders/{sale}/status', [CourierOrderController::class, 'checkStatus'])->name('courier.orders.status');

        // ── Bulk Order Printing ───────────────────────────────────────────────
        Route::get('/sales/bulk-print', [SaleController::class, 'bulkPrint'])->name('sales.bulk-print');

        // ── Branch-specific routes ─────────────────────────────────────────
        Route::prefix('branch/{branch}')->middleware('branch.access')->name('branch.')->group(function () {

            // Dashboard — view_dashboard
            Route::get('/dashboard', [DashboardController::class, 'index'])
                ->middleware('permission:view_dashboard')
                ->name('dashboard');

            // POS — create_sales
            Route::middleware('permission:create_sales')->group(function () {
                Route::get('/pos',          [POSController::class, 'index'])->name('pos.index');
                Route::post('/pos/sale',    [POSController::class, 'storeSale'])->name('pos.sale');
                Route::get('/pos/products', [POSController::class, 'products'])->name('pos.products');
                Route::get('/pos/customers',[POSController::class, 'customers'])->name('pos.customers');
            });

            // Sales: view — view_sales
            Route::middleware('permission:view_sales')->group(function () {
                Route::get('/sales',        [SaleController::class, 'index'])->name('sales.index');
                Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
                Route::post('/sales-fraud-check', [SaleController::class, 'fraudCheck'])->name('sales.fraud-check');
                Route::post('/sales-bd-courier-check', [SaleController::class, 'bdCourierCheck'])->name('sales.bd-courier-check');
                Route::get('/sales/{sale}/notes',  [SaleController::class, 'getNotes'])->name('sales.notes.index');
                Route::post('/sales/{sale}/notes', [SaleController::class, 'addNote'])->name('sales.notes.store');
                Route::post('/sales/{sale}/send-courier', [CourierOrderController::class, 'sendToCourier'])->name('sales.send-courier');
                Route::get('/sales/{sale}/courier-status', [CourierOrderController::class, 'checkStatus'])->name('sales.courier-status');
            });
            // Sales: edit/pay/status — manage_payments
            Route::middleware('permission:manage_payments')->group(function () {
                Route::get('/sales/{sale}/edit',         [SaleController::class, 'edit'])->name('sales.edit');
                Route::put('/sales/{sale}',              [SaleController::class, 'update'])->name('sales.update');
                Route::post('/sales/{sale}/status',      [SaleController::class, 'updateStatus'])->name('sales.status');
                Route::post('/sales/{sale}/add-payment', [SaleController::class, 'addPayment'])->name('sales.add-payment');
                Route::post('/sales/{sale}/return',       [SaleReturnController::class, 'store'])->name('sales.return');
            });

            // Categories are managed only from the Warehouse now — see admin.warehouse.categories.*

            // Products — manage_products
            // New products are only ever created at the Warehouse now (see admin.warehouse.products.*),
            // and deleting a product also only happens there — branches just edit/manage and print
            // barcodes for the stock they've already been distributed.
            Route::middleware('permission:manage_products')->group(function () {
                Route::get('/products',                    [ProductController::class, 'index'])->name('products.index');
                Route::get('/products/create',             [ProductController::class, 'create'])->name('products.create');
                Route::post('/products',                   [ProductController::class, 'store'])->name('products.store');
                Route::get('/products/print-barcodes',     [ProductController::class, 'bulkBarcode'])->name('products.barcode-bulk');
                Route::get('/products/{product}/edit',     [ProductController::class, 'edit'])->name('products.edit');
                Route::get('/products/{product}/barcode',  [ProductController::class, 'showBarcode'])->name('products.barcode-show');
                Route::post('/products/{product}',         [ProductController::class, 'update'])->name('products.update');
                Route::post('/products/{product}/barcode', [ProductController::class, 'generateBarcode'])->name('products.barcode');
            });

            // Customers — manage_customers
            Route::middleware('permission:manage_customers')->group(function () {
                Route::get('/customers',                   [CustomerController::class, 'index'])->name('customers.index');
                Route::post('/customers',                  [CustomerController::class, 'store'])->name('customers.store');
                Route::put('/customers/{customer}',        [CustomerController::class, 'update'])->name('customers.update');
                Route::delete('/customers/{customer}',     [CustomerController::class, 'destroy'])->name('customers.destroy');
                Route::get('/customers/{customer}/report', [CustomerController::class, 'report'])->name('customers.report');
            });

            // Stock — manage_inventory
            Route::get('/stock', [StockController::class, 'index'])
                ->middleware('permission:manage_inventory')
                ->name('stock.index');

            // Finance — manage_payments
            Route::middleware('permission:manage_payments')->group(function () {
                Route::get('/finance',                          [FinanceController::class, 'index'])->name('finance.index');
                Route::post('/finance/categories',              [FinanceController::class, 'storeCategory'])->name('finance.categories.store');
                Route::delete('/finance/categories/{category}', [FinanceController::class, 'destroyCategory'])->name('finance.categories.destroy');
                Route::post('/finance',                         [FinanceController::class, 'store'])->name('finance.store');
                Route::delete('/finance/{incomeExpense}',       [FinanceController::class, 'destroy'])->name('finance.destroy');
            });

            // Reports — view_reports
            Route::middleware('permission:view_reports')->group(function () {
                Route::get('/reports',     [ReportController::class, 'index'])->name('reports.index');
                Route::get('/fraud-check', [FraudCheckController::class, 'index'])->name('fraud-check.index');
            });

            // Staff — manage_users
            Route::middleware('permission:manage_users')->group(function () {
                Route::get('/staff',           [UserController::class, 'index'])->name('staff.index');
                Route::post('/staff',          [UserController::class, 'store'])->name('staff.store');
                Route::put('/staff/{user}',    [UserController::class, 'update'])->name('staff.update');
                Route::delete('/staff/{user}', [UserController::class, 'destroy'])->name('staff.destroy');
            });

            // Settings — always accessible (profile/password); branch update needs manage_users
            Route::get('/settings',           [SettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings/profile',  [SettingsController::class, 'updateProfile'])->name('settings.profile');
            Route::post('/settings/password', [SettingsController::class, 'changePassword'])->name('settings.password');
            Route::post('/settings/branch',   [SettingsController::class, 'updateBranch'])
                ->middleware('permission:manage_users')
                ->name('settings.branch');
        });

        // ── Warehouse routes — its own controllers (App\Http\Controllers\Warehouse\*)
        // and views (resources/views/warehouse/*), kept fully separate from the branch
        // controllers/views above so neither side carries is_warehouse conditionals.
        // Clean /admin/warehouse/... URL instead of /admin/branch/{id}/... since there's
        // only ever one Warehouse. {branch} is a real (but value-locked) URI segment
        // — not a Route::defaults() — so it lands in the same argument position as
        // the numeric {branch} does on the routes above; Vendor::resolveRouteBinding()
        // resolves the literal word "warehouse" to the actual Warehouse vendor.
        Route::prefix('{branch}')->where(['branch' => 'warehouse'])->middleware('branch.access')->name('admin.warehouse.')->group(function () {
            Route::get('/dashboard', [WarehouseDashboardController::class, 'index'])
                ->middleware('permission:view_dashboard')
                ->name('dashboard');

            // Products — the Warehouse is the only place new products get created (and deleted);
            // branches just receive distributed clones and manage the stock they've been given.
            Route::middleware('permission:manage_products')->group(function () {
                Route::get('/products',                    [WarehouseProductController::class, 'index'])->name('products.index');
                Route::get('/products/create',              [WarehouseProductController::class, 'create'])->name('products.create');
                Route::post('/products',                    [WarehouseProductController::class, 'store'])->name('products.store');
                Route::get('/products/{product}/edit',      [WarehouseProductController::class, 'edit'])->name('products.edit');
                Route::get('/products/{product}/barcode',   [WarehouseProductController::class, 'showBarcode'])->name('products.barcode-show');
                Route::put('/products/{product}',           [WarehouseProductController::class, 'update'])->name('products.update');
                Route::post('/products/{product}/barcode',  [WarehouseProductController::class, 'generateBarcode'])->name('products.barcode');
                Route::delete('/products/{product}',        [WarehouseProductController::class, 'destroy'])->name('products.destroy');
                Route::get('/products/{product}/report',    [WarehouseProductController::class, 'report'])->name('products.report');
            });

            // Categories — no branch system anymore; managed only from here, one edit/delete
            // applies system-wide (every vendor's matching-named row stays in sync).
            Route::middleware('permission:manage_categories')->group(function () {
                Route::get('/categories',                                [WarehouseCategoryController::class, 'index'])->name('categories.index');
                Route::post('/categories',                                [WarehouseCategoryController::class, 'store'])->name('categories.store');
                Route::put('/categories/{category}',                      [WarehouseCategoryController::class, 'update'])->name('categories.update');
                Route::delete('/categories/{category}',                   [WarehouseCategoryController::class, 'destroy'])->name('categories.destroy');
                Route::post('/categories/{category}/toggle-homepage',     [WarehouseCategoryController::class, 'toggleHomepage'])->name('categories.toggle-homepage');
                Route::post('/categories/{category}/reorder-homepage',    [WarehouseCategoryController::class, 'reorderHomepage'])->name('categories.reorder-homepage');
            });

            Route::middleware('permission:manage_suppliers')->group(function () {
                Route::get('/suppliers',                   [WarehouseSupplierController::class, 'index'])->name('suppliers.index');
                Route::post('/suppliers',                  [WarehouseSupplierController::class, 'store'])->name('suppliers.store');
                Route::put('/suppliers/{supplier}',        [WarehouseSupplierController::class, 'update'])->name('suppliers.update');
                Route::delete('/suppliers/{supplier}',     [WarehouseSupplierController::class, 'destroy'])->name('suppliers.destroy');
                Route::get('/suppliers/{supplier}/report', [WarehouseSupplierController::class, 'report'])->name('suppliers.report');

                Route::get('/purchases',                     [WarehousePurchaseController::class, 'index'])->name('purchases.index');
                Route::get('/purchases/create',              [WarehousePurchaseController::class, 'create'])->name('purchases.create');
                Route::post('/purchases',                    [WarehousePurchaseController::class, 'store'])->name('purchases.store');
                Route::get('/purchases/{purchase}',          [WarehousePurchaseController::class, 'show'])->name('purchases.show');
                Route::get('/purchases/{purchase}/edit',     [WarehousePurchaseController::class, 'edit'])->name('purchases.edit');
                Route::post('/purchases/{purchase}',         [WarehousePurchaseController::class, 'update'])->name('purchases.update');
                Route::post('/purchases/{purchase}/payment', [WarehousePurchaseController::class, 'updatePayment'])->name('purchases.payment');
                Route::post('/purchases/{purchase}/return',  [WarehousePurchaseReturnController::class, 'store'])->name('purchases.return');
                Route::delete('/purchases/{purchase}',       [WarehousePurchaseController::class, 'destroy'])->name('purchases.destroy');

                Route::get('/transfer',  [WarehouseTransferController::class, 'index'])->name('transfer');
                Route::post('/transfer', [WarehouseTransferController::class, 'store'])->name('transfer.store');
            });

            Route::get('/stock', [WarehouseStockController::class, 'index'])
                ->middleware('permission:manage_inventory')
                ->name('stock.index');

            Route::middleware('permission:manage_users')->group(function () {
                Route::get('/staff',           [WarehouseStaffController::class, 'index'])->name('staff.index');
                Route::post('/staff',          [WarehouseStaffController::class, 'store'])->name('staff.store');
                Route::put('/staff/{user}',    [WarehouseStaffController::class, 'update'])->name('staff.update');
                Route::delete('/staff/{user}', [WarehouseStaffController::class, 'destroy'])->name('staff.destroy');
            });

            Route::get('/settings',           [WarehouseSettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings/profile',  [WarehouseSettingsController::class, 'updateProfile'])->name('settings.profile');
            Route::post('/settings/password', [WarehouseSettingsController::class, 'changePassword'])->name('settings.password');
            Route::post('/settings/branch',   [WarehouseSettingsController::class, 'updateBranch'])
                ->middleware('permission:manage_users')
                ->name('settings.branch');

            // ── Variant Attributes — Colors & Sizes ──────────────────────
            Route::middleware('permission:manage_products')->prefix('variant-attributes')->name('variant-attributes.')->group(function () {
                Route::get('/',                                             [WarehouseVariantAttributeController::class, 'index'])->name('index');
                // Colors
                Route::post('/colors',                                      [WarehouseVariantAttributeController::class, 'storeColor'])->name('colors.store');
                Route::put('/colors/{color}',                               [WarehouseVariantAttributeController::class, 'updateColor'])->name('colors.update');
                Route::delete('/colors/{color}',                            [WarehouseVariantAttributeController::class, 'destroyColor'])->name('colors.destroy');
                // Sizes
                Route::post('/sizes',                                       [WarehouseVariantAttributeController::class, 'storeSize'])->name('sizes.store');
                Route::put('/sizes/{size}',                                 [WarehouseVariantAttributeController::class, 'updateSize'])->name('sizes.update');
                Route::delete('/sizes/{size}',                              [WarehouseVariantAttributeController::class, 'destroySize'])->name('sizes.destroy');
            });
        });
    });
});
