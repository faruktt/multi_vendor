<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\IncomeExpense;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Vendor $branch)
    {
        $now       = Carbon::now();
        $today     = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();

        // ── Sales stats ───────────────────────────────────────────
        $saleQ = fn() => Sale::withoutGlobalScopes()->where('vendor_id', $branch->id);

        $todayRev    = round($saleQ()->whereDate('created_at', $today)->sum('total'), 2);
        $todayOrders = $saleQ()->whereDate('created_at', $today)->count();
        $yesterdayRev = round($saleQ()->whereDate('created_at', $yesterday)->sum('total'), 2);
        $todayVsYest  = $yesterdayRev > 0 ? round((($todayRev - $yesterdayRev) / $yesterdayRev) * 100, 1) : null;

        $monthStart   = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd   = $now->copy()->subMonth()->endOfMonth();

        $monthRev     = round($saleQ()->where('created_at', '>=', $monthStart)->sum('total'), 2);
        $monthOrders  = $saleQ()->where('created_at', '>=', $monthStart)->count();
        $lastMonthRev = round($saleQ()->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total'), 2);
        $monthVsLast  = $lastMonthRev > 0 ? round((($monthRev - $lastMonthRev) / $lastMonthRev) * 100, 1) : null;

        $allRevenue   = round($saleQ()->sum('total'), 2);
        $allOrders    = $saleQ()->count();
        $totalDue     = round($saleQ()->sum('due_amount'), 2);
        $totalPaid    = round($saleQ()->sum('paid_amount'), 2);

        // Payment status counts
        $paymentStatus = $saleQ()
            ->selectRaw('payment_status, COUNT(*) as cnt, SUM(total) as rev')
            ->groupBy('payment_status')
            ->get()->keyBy('payment_status');

        // Payment methods
        $paymentMethods = $saleQ()
            ->selectRaw('payment_method, COUNT(*) as cnt, SUM(total) as rev')
            ->groupBy('payment_method')
            ->orderByDesc('rev')
            ->get();

        // ── Inventory ─────────────────────────────────────────────
        $prodQ         = fn() => $branch->products();
        $totalProducts = $prodQ()->count();
        $outOfStock    = $prodQ()->where('stock_qty', '<=', 0)->count();
        $lowStock      = $prodQ()->where('stock_qty', '>', 0)->where('stock_qty', '<=', 10)->count();
        $stockValue    = round($prodQ()->selectRaw('SUM(stock_qty * cost_price) as v')->value('v') ?? 0, 2);
        $totalStock    = $prodQ()->sum('stock_qty');

        // Low stock items for alert list
        $lowStockItems = $branch->products()
            ->where('stock_qty', '<=', 10)
            ->orderBy('stock_qty')
            ->limit(5)
            ->get(['id','name','stock_qty','unit']);

        // ── Purchases ─────────────────────────────────────────────
        $monthPurchases = round(Purchase::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('created_at', '>=', $monthStart)
            ->sum('total'), 2);
        $supplierDue = round(Purchase::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)->sum('due_amount'), 2);

        // ── Finance this month ────────────────────────────────────
        $finQ = fn() => IncomeExpense::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year);
        $monthIncome  = round($finQ()->where('type','income')->sum('amount'), 2);
        $monthExpense = round($finQ()->where('type','expense')->sum('amount'), 2);

        // ── Customers ─────────────────────────────────────────────
        $totalCustomers = $branch->customers()->count();
        $newThisMonth   = $branch->customers()->where('created_at', '>=', $monthStart)->count();

        // ── 30-day revenue chart ──────────────────────────────────
        $last30 = Sale::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = $chartRevenue = $chartOrders = [];
        for ($i = 29; $i >= 0; $i--) {
            $date           = $now->copy()->subDays($i)->toDateString();
            $chartLabels[]  = Carbon::parse($date)->format('d M');
            $chartRevenue[] = round($last30->get($date)?->revenue ?? 0, 0);
            $chartOrders[]  = $last30->get($date)?->orders ?? 0;
        }

        // ── Top products ──────────────────────────────────────────
        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.vendor_id', $branch->id)
            ->selectRaw('products.name, SUM(sale_items.quantity) as total_sold, SUM(sale_items.subtotal) as revenue')
            ->groupBy('sale_items.product_id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // ── Recent sales ──────────────────────────────────────────
        $recentSales = $branch->sales()
            ->with(['customer','createdBy'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard.index', compact(
            'branch',
            'todayRev','todayOrders','todayVsYest',
            'monthRev','monthOrders','monthVsLast','lastMonthRev',
            'allRevenue','allOrders','totalDue','totalPaid',
            'paymentStatus','paymentMethods',
            'totalProducts','outOfStock','lowStock','stockValue','totalStock','lowStockItems',
            'monthPurchases','supplierDue',
            'monthIncome','monthExpense',
            'totalCustomers','newThisMonth',
            'chartLabels','chartRevenue','chartOrders',
            'topProducts','recentSales'
        ));
    }
}
