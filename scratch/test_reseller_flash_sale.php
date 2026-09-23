<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\FlashSale;
use App\Models\Vendor;
use App\Models\User;
use App\Http\Controllers\Reseller\CartController as ResellerCartController;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

echo "=== 1. FETCHING PRODUCT AND SETTING UP PRICES ===\n";
$product = Product::withoutGlobalScopes()->whereNotNull('price')->first();
$product->price = 1000.00;
$product->reseller_price = 700.00;
$product->stock_qty = 50;
$product->save();

echo "Product ID: {$product->id}, Name: {$product->name}\n";
echo "Retail Price: {$product->price}, Reseller Price: {$product->reseller_price}\n";

// Clear previous flash sales for this product
FlashSale::where('product_id', $product->id)->delete();

echo "\n=== 2. CREATING CUSTOMER FLASH SALE (৳800) ===\n";
$customerSale = FlashSale::create([
    'product_id'          => $product->id,
    'target_audience'     => 'customer',
    'flash_price'         => 800.00,
    'discount_percentage' => 20.00,
    'start_time'          => now()->subHour(),
    'end_time'            => now()->addDays(2),
    'is_active'           => true,
]);

$product->refresh();
echo "Customer Flash Sale active: " . ($product->isOnCustomerFlashSale() ? 'YES' : 'NO') . "\n";
echo "Reseller Flash Sale active: " . ($product->isOnResellerFlashSale() ? 'YES' : 'NO') . "\n";
echo "Customer Effective Price: {$product->effective_price} (Expected 800)\n";
echo "Reseller Effective Price: {$product->effective_reseller_price} (Expected 700)\n";

if ((float)$product->effective_price === 800.0 && (float)$product->effective_reseller_price === 700.0) {
    echo "SUCCESS: Customer sale affects ONLY customers!\n";
} else {
    echo "ERROR: Audience isolation failed!\n";
}

echo "\n=== 3. CREATING RESELLER FLASH SALE (৳550) ===\n";
$resellerSale = FlashSale::create([
    'product_id'          => $product->id,
    'target_audience'     => 'reseller',
    'flash_price'         => 550.00,
    'discount_percentage' => 21.43, // (700-550)/700
    'start_time'          => now()->subHour(),
    'end_time'            => now()->addDays(2),
    'is_active'           => true,
]);

$product->refresh();
echo "Customer Flash Sale active: " . ($product->isOnCustomerFlashSale() ? 'YES' : 'NO') . "\n";
echo "Reseller Flash Sale active: " . ($product->isOnResellerFlashSale() ? 'YES' : 'NO') . "\n";
echo "Customer Effective Price: {$product->effective_price} (Expected 800)\n";
echo "Reseller Effective Price: {$product->effective_reseller_price} (Expected 550)\n";

if ((float)$product->effective_price === 800.0 && (float)$product->effective_reseller_price === 550.0) {
    echo "SUCCESS: Both Customer & Reseller have independent flash sale prices!\n";
} else {
    echo "ERROR: Pricing mismatch!\n";
}

echo "\n=== 4. TESTING RESELLER CART RESOLUTION ===\n";
Session::put(ResellerCartController::SESSION_KEY, [
    $product->id . ':null' => [
        'product_id' => $product->id,
        'variant_id' => null,
        'qty'        => 3,
    ]
]);

$resellerCartLines = ResellerCartController::lines();
$cartItem = $resellerCartLines->first();

if ($cartItem) {
    echo "Cart Item Price: {$cartItem['price']} (Expected 550)\n";
    echo "Cart Item Regular Reseller Price: {$cartItem['regular_reseller_price']} (Expected 700)\n";
    echo "Cart Item Subtotal (qty 3): {$cartItem['subtotal']} (Expected 1650)\n";
    echo "Cart Item Is Flash Sale: " . ($cartItem['is_flash_sale'] ? 'YES' : 'NO') . "\n";
    if ((float)$cartItem['price'] === 550.0 && (float)$cartItem['subtotal'] === 1650.0) {
        echo "SUCCESS: Reseller cart resolved at discounted flash price!\n";
    } else {
        echo "ERROR: Reseller cart resolution failed!\n";
    }
}

echo "\n=== 5. TESTING VIEW RENDERING ===\n";
$errors = new \Illuminate\Support\ViewErrorBag;

// Authenticate admin user
$adminUser = User::first();
if ($adminUser) {
    auth()->login($adminUser);
}

// 5a. Admin Flash Sales View
try {
    $adminView = View::make('admin.flash-sales.index', [
        'flashSales' => FlashSale::with('product')->paginate(15),
        'products'   => Product::withoutGlobalScopes()->take(10)->get(),
        'stats'      => [
            'total_deals'    => 2,
            'active_deals'   => 2,
            'customer_deals' => 1,
            'reseller_deals' => 1,
            'expired_deals'  => 0,
            'total_savings'  => 350.00,
            'avg_discount'   => 20.7,
        ],
        'errors' => $errors,
    ])->render();
    echo "SUCCESS: admin/flash-sales/index.blade.php rendered (" . strlen($adminView) . " bytes)\n";
    if (strpos($adminView, 'Reseller Deals') !== false && strpos($adminView, 'Customer Deals') !== false) {
        echo "SUCCESS: Admin view contains Customer and Reseller stats & badges!\n";
    }
} catch (\Throwable $e) {
    echo "ERROR in admin view: " . $e->getMessage() . "\n";
}

// Authenticate reseller user
$resellerUser = \App\Models\Reseller::first();
if (!$resellerUser) {
    $resellerUser = new \App\Models\Reseller([
        'name' => 'Test Reseller',
        'email' => 'testreseller@example.com',
        'phone' => '01700000000',
        'status' => 'active',
    ]);
}
auth('reseller')->setUser($resellerUser);

// 5b. Reseller Products Index View
try {
    $resellerIndex = View::make('reseller.products.index', [
        'products'   => Product::withoutGlobalScopes()->where('id', $product->id)->paginate(24),
        'categories' => collect(),
        'branches'   => collect(),
        'errors'     => $errors,
    ])->render();
    echo "SUCCESS: reseller/products/index.blade.php rendered (" . strlen($resellerIndex) . " bytes)\n";
    if (strpos($resellerIndex, 'FLASH') !== false) {
        echo "SUCCESS: Reseller product card displays FLASH SALE badge and discounted price!\n";
    }
} catch (\Throwable $e) {
    echo "ERROR in reseller index view: " . $e->getMessage() . "\n";
}

// 5c. Reseller Product Show View
try {
    $resellerShow = View::make('reseller.products.show', [
        'product' => $product,
        'related' => collect(),
        'errors'  => $errors,
    ])->render();
    echo "SUCCESS: reseller/products/show.blade.php rendered (" . strlen($resellerShow) . " bytes)\n";
    if (strpos($resellerShow, 'Exclusive Reseller Flash Deal') !== false) {
        echo "SUCCESS: Reseller product details contains Exclusive Reseller Flash Deal banner!\n";
    }
} catch (\Throwable $e) {
    echo "ERROR in reseller show view: " . $e->getMessage() . "\n";
}

echo "\nALL RESELLER & CUSTOMER FLASH SALE TESTS COMPLETED!\n";
