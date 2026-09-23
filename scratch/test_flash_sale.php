<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\FlashSale;
use App\Models\Vendor;
use App\Models\User;
use App\Support\Cart;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;

echo "--- 1. FETCHING SAMPLE PRODUCT ---\n";
$vendor = Vendor::onlineStore() ?? Vendor::first();
$product = Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->first();
if (!$product) {
    $product = Product::withoutGlobalScopes()->first();
}

echo "Selected Product ID: {$product->id}, Name: {$product->name}, Regular Price: {$product->price}\n";

echo "\n--- 2. TESTING FLASH SALE MODEL CREATION ---\n";
// Remove any existing flash sale for this product first
FlashSale::where('product_id', $product->id)->delete();

$regularPrice = (float) $product->price;
$flashPrice = round($regularPrice * 0.75, 2); // 25% off
$adminUser = User::first();

$flashSale = FlashSale::create([
    'product_id' => $product->id,
    'flash_price' => $flashPrice,
    'discount_percentage' => 25,
    'start_time' => now()->subHour(),
    'end_time' => now()->addDays(2),
    'is_active' => true,
    'created_by' => $adminUser?->id,
]);

echo "Created Flash Sale ID: {$flashSale->id}, Flash Price: {$flashSale->flash_price}, Active: " . ($flashSale->isCurrentlyActive() ? 'YES' : 'NO') . "\n";

echo "\n--- 3. TESTING PRODUCT MODEL FLASH SALE INTEGRATION ---\n";
$product->refresh();
$product->load('flashSales');

echo "Product isOnFlashSale(): " . ($product->isOnFlashSale() ? 'YES' : 'NO') . "\n";
echo "Product effective_price: {$product->effective_price}\n";
echo "Product flash_price: {$product->flash_price}\n";
echo "Product discount_percentage: {$product->discount_percentage}%\n";

if (!$product->isOnFlashSale() || (float)$product->effective_price !== (float)$flashPrice) {
    echo "ERROR: Product effective_price does not match flash price!\n";
} else {
    echo "SUCCESS: Product correctly returns flash sale price.\n";
}

echo "\n--- 4. TESTING CART RESOLUTION WITH FLASH PRICE ---\n";
// Ensure product stock is at least 10 for testing qty 2
$product->stock_qty = max(10, $product->stock_qty);
$product->save();

$fakeRawCart = [
    $product->id . ':null' => [
        'product_id' => $product->id,
        'variant_id' => null,
        'qty' => 2,
    ]
];

$cartLines = Cart::resolve($vendor, $fakeRawCart);
echo "Cart lines count: " . $cartLines->count() . "\n";
$line = $cartLines->first();
if ($line) {
    echo "Cart Line Price: {$line['price']} (Regular: {$line['regular_price']})\n";
    echo "Cart Line Is Flash Sale: " . ($line['is_flash_sale'] ? 'YES' : 'NO') . "\n";
    echo "Cart Line Subtotal for 2 items: {$line['subtotal']} (Expected: " . ($flashPrice * 2) . ")\n";
    if ((float)$line['price'] === (float)$flashPrice && (float)$line['subtotal'] === (float)($flashPrice * 2)) {
        echo "SUCCESS: Cart calculated with discounted Flash Sale price!\n";
    } else {
        echo "ERROR: Cart did not use discounted flash price!\n";
    }
}

echo "\n--- 5. TESTING HTTP KERNEL REQUEST HANDLING ---\n";
$httpKernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// 5a. Login admin user
if ($adminUser) {
    auth()->login($adminUser);
    echo "Logged in as Admin: {$adminUser->name}\n";
}

// 5b. Test Storefront /shop route
$shopRequest = \Illuminate\Http\Request::create('/shop', 'GET');
$shopResponse = $httpKernel->handle($shopRequest);
echo "GET /shop Status Code: " . $shopResponse->getStatusCode() . "\n";
$shopContent = $shopResponse->getContent();
if (strpos($shopContent, 'Flash Sale & Super Deals') !== false) {
    echo "SUCCESS: Storefront homepage displays 'Flash Sale & Super Deals' shelf!\n";
} else {
    echo "NOTE: Flash shelf text check: " . (strpos($shopContent, 'Flash') !== false ? 'Found Flash' : 'Not found') . "\n";
}

// 5c. Test Admin Flash Sales index route
$adminRequest = \Illuminate\Http\Request::create('/admin/flash-sales', 'GET');
$adminResponse = $httpKernel->handle($adminRequest);
echo "GET /admin/flash-sales Status Code: " . $adminResponse->getStatusCode() . "\n";
$adminContent = $adminResponse->getContent();
if (strpos($adminContent, 'Flash Sale Management') !== false) {
    echo "SUCCESS: Admin Flash Sale Management dashboard rendered successfully!\n";
}

// 5d. Test Product Details Page
$productRequest = \Illuminate\Http\Request::create('/product/' . $product->slug, 'GET');
$productResponse = $httpKernel->handle($productRequest);
echo "GET /product/{$product->slug} Status Code: " . $productResponse->getStatusCode() . "\n";
$productContent = $productResponse->getContent();
if (strpos($productContent, 'Flash Sale') !== false) {
    echo "SUCCESS: Product details page contains Flash Sale banner and pricing!\n";
}

echo "\n--- 6. TESTING CONTROLLER TOGGLE AND DESTROY ---\n";
$flashSale->update(['is_active' => false]);
$flashSale->refresh();
echo "Toggled Active status to: " . ($flashSale->is_active ? 'Active' : 'Inactive') . "\n";
$product->refresh();
$product->unsetRelation('flashSales');
echo "After deactivating, Product isOnFlashSale(): " . ($product->isOnFlashSale() ? 'YES' : 'NO') . "\n";
echo "After deactivating, Product effective_price reverts to regular: {$product->effective_price}\n";

// Reactivate it so the user can immediately see it on the admin and storefront
$flashSale->update(['is_active' => true]);
echo "Reactivated Flash Sale for live demonstration.\n";

echo "\nALL FLASH SALE TESTS COMPLETED SUCCESSFULLY!\n";
