<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Vendor;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Warehouse has no sales/customers/finance — its dashboard centers on
     * purchasing, stock levels, and what's been transferred out to branches.
     */
    public function index(Vendor $branch)
    {
        $now            = Carbon::now();
        $monthStart     = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd   = $now->copy()->subMonth()->endOfMonth();

        // ── Inventory ─────────────────────────────────────────────
        $prodQ         = fn() => $branch->products();
        $totalProducts = $prodQ()->count();
        $outOfStock    = $prodQ()->where('stock_qty', '<=', 0)->count();
        $lowStock      = $prodQ()->where('stock_qty', '>', 0)->where('stock_qty', '<=', 10)->count();
        $stockValue    = round($prodQ()->selectRaw('SUM(stock_qty * cost_price) as v')->value('v') ?? 0, 2);
        $totalStock    = $prodQ()->sum('stock_qty');

        $lowStockItems = $branch->products()
            ->where('stock_qty', '<=', 10)
            ->orderBy('stock_qty')
            ->limit(5)
            ->get(['id', 'name', 'stock_qty', 'unit']);

        // ── Purchases ─────────────────────────────────────────────
        $purchQ = fn() => Purchase::withoutGlobalScopes()->where('vendor_id', $branch->id);

        $monthPurchases     = round($purchQ()->where('created_at', '>=', $monthStart)->sum('total'), 2);
        $lastMonthPurchases = round($purchQ()->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total'), 2);
        $monthVsLast        = $lastMonthPurchases > 0 ? round((($monthPurchases - $lastMonthPurchases) / $lastMonthPurchases) * 100, 1) : null;
        $allPurchases       = round($purchQ()->sum('total'), 2);
        $totalOrders        = $purchQ()->count();
        $supplierDue        = round($purchQ()->sum('due_amount'), 2);

        $recentPurchases = $branch->purchases()
            ->with(['supplier', 'createdBy'])
            ->latest()
            ->limit(8)
            ->get();

        // ── Transfers out to branches ───────────────────────────────
        $transferQ = fn() => StockMovement::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('type', 'out')
            ->where('reference_type', 'transfer');

        $monthTransferredQty = (int) $transferQ()->where('created_at', '>=', $monthStart)->sum('quantity');
        $allTransferredQty   = (int) $transferQ()->sum('quantity');

        $recentTransfers = $transferQ()
            ->with('product')
            ->latest()
            ->limit(8)
            ->get();

        // ── 30-day purchase chart ────────────────────────────────────
        $last30 = $purchQ()
            ->where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(total) as amount, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = $chartAmounts = $chartOrders = [];
        for ($i = 29; $i >= 0; $i--) {
            $date          = $now->copy()->subDays($i)->toDateString();
            $chartLabels[] = Carbon::parse($date)->format('d M');
            $chartAmounts[] = round($last30->get($date)?->amount ?? 0, 0);
            $chartOrders[]  = $last30->get($date)?->orders ?? 0;
        }

        return view('warehouse.dashboard', compact(
            'branch',
            'totalProducts', 'outOfStock', 'lowStock', 'stockValue', 'totalStock', 'lowStockItems',
            'monthPurchases', 'monthVsLast', 'allPurchases', 'totalOrders', 'supplierDue',
            'recentPurchases',
            'monthTransferredQty', 'allTransferredQty', 'recentTransfers',
            'chartLabels', 'chartAmounts', 'chartOrders'
        ));
    }
}
