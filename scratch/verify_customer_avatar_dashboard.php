<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;

echo "=== Verifying Customer Account & Avatar Feature ===\n";

$branch = Vendor::onlineStore();
echo "Online branch: " . ($branch ? $branch->name : 'None') . "\n";
view()->share('branch', $branch);
view()->share('errors', new \Illuminate\Support\ViewErrorBag);

$customer = Customer::withoutGlobalScopes()->first();
if (!$customer) {
    echo "Creating a test customer...\n";
    $customer = Customer::create([
        'vendor_id' => $branch->id,
        'name'      => 'MD Fayaz',
        'phone'     => '01700000000',
        'email'     => 'fayaz@example.com',
        'password'  => bcrypt('secret123'),
        'status'    => 'active',
    ]);
}

echo "Customer found: ID={$customer->id}, Name={$customer->name}, Image=" . ($customer->image ?? 'null') . "\n";
echo "Avatar URL: " . ($customer->avatar_url ?? 'null') . "\n";

// Test logging in as customer
Auth::guard('customer')->setUser($customer);
echo "Auth guard customer check: " . (Auth::guard('customer')->check() ? 'YES' : 'NO') . "\n";

// Test rendering shop.account.profile
try {
    $profileHtml = view('shop.account.profile', compact('customer', 'branch'))->render();
    echo "shop.account.profile view rendered successfully! Length: " . strlen($profileHtml) . " bytes\n";
    if (str_contains($profileHtml, 'name="image"')) {
        echo "PASS: Profile has file input for 'image'!\n";
    }
    if (str_contains($profileHtml, 'enctype="multipart/form-data"')) {
        echo "PASS: Profile form has multipart/form-data!\n";
    }
} catch (\Throwable $e) {
    echo "ERROR rendering profile: " . $e->getMessage() . "\n";
}

// Test rendering shop.account.dashboard
try {
    $totalOrders = 5;
    $totalSpent = 12500;
    $pendingOrders = 1;
    $deliveredOrders = 4;
    $recentOrders = collect();
    $dashHtml = view('shop.account.dashboard', compact(
        'customer', 'branch', 'totalOrders', 'totalSpent', 'pendingOrders', 'deliveredOrders', 'recentOrders'
    ))->render();
    echo "shop.account.dashboard view rendered successfully! Length: " . strlen($dashHtml) . " bytes\n";
} catch (\Throwable $e) {
    echo "ERROR rendering dashboard: " . $e->getMessage() . "\n";
}

// Test layout header with avatar vs without avatar
echo "\n--- Testing Header Name Suppression when Image is Set ---\n";
// Case 1: Customer has NO image
$customer->image = null;
$viewNoImg = view('shop.layout', [
    'categories' => collect(),
    'cartCount' => 0,
    'appSettings' => [],
    'whatsappNumber' => '8801700000000',
])->render();
echo "Rendered with NO image: Contains customer name in header? " . (str_contains($viewNoImg, $customer->name) ? 'YES (Expected)' : 'NO') . "\n";

// Case 2: Customer HAS image
$customer->image = 'customers/test_avatar.jpg';
$viewWithImg = view('shop.layout', [
    'categories' => collect(),
    'cartCount' => 0,
    'appSettings' => [],
    'whatsappNumber' => '8801700000000',
])->render();

// Check if customer name appears in the top header button
// In the button: <button ... title="MD Fayaz"> <img src="..." ...> <i class="fas fa-chevron-down"></i> </button>
// Notice the name text span should NOT exist in the button when image exists!
$hasNameSpan = preg_match('/<span class="max-w-\[\d+px\] truncate">/i', $viewWithImg);
echo "Has truncate name span in header button when avatar present? " . ($hasNameSpan ? 'YES (Check)' : 'NO (Correct: Name hidden!)') . "\n";
echo "Avatar image tag present in header? " . (str_contains($viewWithImg, 'customers/test_avatar.jpg') ? 'YES' : 'NO') . "\n";

echo "=== All checks completed! ===\n";
