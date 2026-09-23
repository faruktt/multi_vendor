<?php
// ⚠️ ONE-TIME USE SCRIPT — delete this file from the server immediately after running it.
// Anyone who finds this URL and knows the token below can wipe stock/purchase/sales data.

$SECRET_TOKEN = 'a38b19e365619daa0e608d4ed727a6c9';

if (($_GET['token'] ?? '') !== $SECRET_TOKEN) {
    http_response_code(404);
    exit('Not found.');
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

function h($s) { return htmlspecialchars($s, ENT_QUOTES); }

$productCount  = Product::withoutGlobalScopes()->count();
$purchaseCount = Purchase::withoutGlobalScopes()->count();
$itemCount     = PurchaseItem::count();
$saleCount     = Sale::withoutGlobalScopes()->count();
$saleItemCount = SaleItem::count();
$saleReturnCount = SaleReturn::withoutGlobalScopes()->count();
$movementCount = StockMovement::withoutGlobalScopes()->count();

$confirmed = ($_GET['step'] ?? '') === '2';

if ($confirmed) {
    DB::transaction(function () {
        Product::withoutGlobalScopes()->update(['stock_qty' => 0]);
        ProductVariant::query()->update(['stock_qty' => 0]);

        PurchaseItem::query()->delete();
        Purchase::withoutGlobalScopes()->delete();

        SaleReturnItem::query()->delete();
        SaleReturn::withoutGlobalScopes()->delete();
        SaleItem::query()->delete();
        Sale::withoutGlobalScopes()->delete();

        StockMovement::withoutGlobalScopes()->delete();
    });
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Inventory Reset</title>
<style>
body { font-family: system-ui, sans-serif; max-width: 560px; margin: 60px auto; padding: 0 20px; color: #1e293b; }
.box { border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; }
.warn { background: #fff7ed; border-color: #fed7aa; color: #9a3412; }
.ok { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
ul { line-height: 1.8; }
a.btn { display: inline-block; margin-top: 16px; background: #dc2626; color: #fff; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: bold; }
a.btn.cancel { background: #64748b; margin-left: 8px; }
</style>
</head>
<body>

<?php if (!$confirmed): ?>
    <div class="box warn">
        <h2>⚠️ This will permanently:</h2>
        <ul>
            <li>Set stock to <b>0</b> on all <b><?= h($productCount) ?></b> products (and their variants)</li>
            <li>Delete all <b><?= h($purchaseCount) ?></b> purchases (<b><?= h($itemCount) ?></b> line items)</li>
            <li>Delete all <b><?= h($saleCount) ?></b> sales (<b><?= h($saleItemCount) ?></b> line items, <b><?= h($saleReturnCount) ?></b> returns)</li>
            <li>Delete all <b><?= h($movementCount) ?></b> stock movement history records</li>
        </ul>
        <p><b>This cannot be undone.</b> Only click below if you're sure.</p>
        <a class="btn" href="?token=<?= h($SECRET_TOKEN) ?>&step=2">Yes, delete everything</a>
        <a class="btn cancel" href="javascript:window.close()">Cancel</a>
    </div>
<?php else: ?>
    <div class="box ok">
        <h2>✅ Done</h2>
        <p>Reset <?= h($productCount) ?> products' stock to 0, deleted <?= h($purchaseCount) ?> purchases, <?= h($saleCount) ?> sales, and <?= h($movementCount) ?> stock movements.</p>
        <p><b>Now delete this file (reset-inventory.php) from the server.</b></p>
    </div>
<?php endif; ?>

</body>
</html>
