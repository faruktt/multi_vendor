<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetInventoryData extends Command
{
    protected $signature = 'inventory:reset {--force : Skip the confirmation prompt}';

    protected $description = 'Reset all product stock to 0 and permanently delete every Purchase, Sale, and StockMovement record';

    public function handle(): int
    {
        $productCount   = Product::withoutGlobalScopes()->count();
        $purchaseCount  = Purchase::withoutGlobalScopes()->count();
        $purchaseItems  = PurchaseItem::count();
        $saleCount      = Sale::withoutGlobalScopes()->count();
        $saleItems      = SaleItem::count();
        $saleReturns    = SaleReturn::withoutGlobalScopes()->count();
        $movementCount  = StockMovement::withoutGlobalScopes()->count();

        $this->warn('This will PERMANENTLY:');
        $this->line("  - Set stock_qty to 0 on all {$productCount} products (and their variants)");
        $this->line("  - Delete all {$purchaseCount} purchases ({$purchaseItems} line items)");
        $this->line("  - Delete all {$saleCount} sales ({$saleItems} line items, {$saleReturns} returns)");
        $this->line("  - Delete all {$movementCount} stock movement records");
        $this->newLine();

        if (!$this->option('force') && !$this->confirm('Continue?', false)) {
            $this->info('Cancelled — nothing was changed.');
            return self::SUCCESS;
        }

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

        $this->info("Done. Reset {$productCount} products' stock to 0, deleted {$purchaseCount} purchases, {$saleCount} sales, and {$movementCount} stock movements.");

        return self::SUCCESS;
    }
}
