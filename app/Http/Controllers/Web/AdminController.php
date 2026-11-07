<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\IncomeExpense;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Moderator;
use App\Models\ModeratorWorkSession;
use App\Models\ModeratorWorkLog;
use App\Models\Reseller;
use App\Models\ResellerWithdrawal;
use App\Models\CourierSetting;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $now    = Carbon::now();
        $preset = $request->get('preset', 'this_month');
        [$from, $to] = $this->resolveRange($request, $preset);
        $fromC  = Carbon::parse($from)->startOfDay();
        $toC    = Carbon::parse($to)->endOfDay();

        // ── Today / yesterday quick stats (always fixed) ──────────
        $today     = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();
        $todayRev  = Sale::withoutGlobalScopes()->completedOrPos()->whereDate('created_at', $today)->sum('total');
        $yesterdayRev = Sale::withoutGlobalScopes()->completedOrPos()->whereDate('created_at', $yesterday)->sum('total');

        // ── Period sales ──────────────────────────────────────────
        $periodSales   = Sale::withoutGlobalScopes()->whereBetween('created_at', [$fromC, $toC]);
        $eligiblePeriodSales = Sale::withoutGlobalScopes()->completedOrPos()->whereBetween('created_at', [$fromC, $toC]);
        $revenue       = round($eligiblePeriodSales->sum('total'), 2);
        $orders        = $periodSales->count();
        $periodPaid    = round($eligiblePeriodSales->sum('paid_amount'), 2);
        $periodDue     = round($eligiblePeriodSales->sum('due_amount'), 2);

        // ── Previous period comparison ────────────────────────────
        $diffDays = max(1, $fromC->diffInDays($toC));
        $prevFrom = $fromC->copy()->subDays($diffDays)->startOfDay();
        $prevTo   = $fromC->copy()->subDay()->endOfDay();
        $prevRev  = round(Sale::withoutGlobalScopes()->completedOrPos()->whereBetween('created_at', [$prevFrom, $prevTo])->sum('total'), 2);
        $revChange = $prevRev > 0 ? round((($revenue - $prevRev) / $prevRev) * 100, 1) : null;

        // ── Period purchases ──────────────────────────────────────
        $periodPurchases = Purchase::withoutGlobalScopes()->whereBetween('created_at', [$fromC, $toC]);
        $purchases       = round($periodPurchases->sum('total'), 2);
        $supplierDue     = round(Purchase::withoutGlobalScopes()->sum('due_amount'), 2);

        // ── Finance income/expense ────────────────────────────────
        $finQ    = fn() => IncomeExpense::withoutGlobalScopes()->whereBetween('date', [$from, $to]);
        $income  = round($finQ()->where('type','income')->sum('amount'), 2);
        $expense = round($finQ()->where('type','expense')->sum('amount'), 2);

        // ── All-time totals ───────────────────────────────────────
        $allRevenue  = round(Sale::withoutGlobalScopes()->completedOrPos()->sum('total'), 2);
        $allOrders   = Sale::withoutGlobalScopes()->count();
        $allDue      = round(Sale::withoutGlobalScopes()->completedOrPos()->sum('due_amount'), 2);

        // ── P&L statement ─────────────────────────────────────────
        $grossProfit    = round($revenue - $purchases, 2);
        $totalIncome    = round($revenue + $income, 2);
        $totalCosts     = round($purchases + $expense, 2);
        $netProfit      = round($grossProfit + $income - $expense, 2);

        $statement = compact(
            'revenue','orders','periodPaid','periodDue',
            'purchases','supplierDue',
            'income','expense',
            'grossProfit','totalIncome','totalCosts','netProfit'
        );

        // ── Per-branch stats (aggregated) ─────────────────────────
        $salePeriodByBranch = Sale::withoutGlobalScopes()
            ->completedOrPos()
            ->whereBetween('created_at', [$fromC, $toC])
            ->selectRaw('vendor_id, SUM(total) as revenue, COUNT(*) as orders, SUM(due_amount) as customer_due')
            ->groupBy('vendor_id')->get()->keyBy('vendor_id');

        $saleAllByBranch = Sale::withoutGlobalScopes()
            ->completedOrPos()
            ->selectRaw('vendor_id, SUM(total) as revenue, COUNT(*) as orders, SUM(due_amount) as customer_due')
            ->groupBy('vendor_id')->get()->keyBy('vendor_id');

        $prevSaleByBranch = Sale::withoutGlobalScopes()
            ->completedOrPos()
            ->whereBetween('created_at', [$prevFrom, $prevTo])
            ->selectRaw('vendor_id, SUM(total) as revenue')
            ->groupBy('vendor_id')->get()->keyBy('vendor_id');

        $purchaseByBranch = Purchase::withoutGlobalScopes()
            ->selectRaw('vendor_id, SUM(total) as total_purchases, SUM(due_amount) as supplier_due')
            ->groupBy('vendor_id')->get()->keyBy('vendor_id');

        $stockByBranch = DB::table('products')
            ->selectRaw('vendor_id, SUM(stock_qty*cost_price) as stock_value, COUNT(*) as product_count, SUM(CASE WHEN stock_qty<=0 THEN 1 ELSE 0 END) as out_of_stock, SUM(CASE WHEN stock_qty>0 AND stock_qty<=10 THEN 1 ELSE 0 END) as low_stock')
            ->groupBy('vendor_id')->get()->keyBy('vendor_id');

        $customerByBranch = DB::table('customers')
            ->selectRaw('vendor_id, COUNT(*) as count')
            ->groupBy('vendor_id')->get()->keyBy('vendor_id');

        $supplierByBranch = DB::table('suppliers')
            ->selectRaw('vendor_id, COUNT(*) as count')
            ->groupBy('vendor_id')->get()->keyBy('vendor_id');

        // Warehouse isn't an ordinary selling branch — its stats here would just be zeroes/noise,
        // so the overview list excludes it. Named distinctly from "branches" — the global view
        // composer injects the full vendor list under that name for the sidebar, and would
        // otherwise clobber this filtered one at render time.
        $branchList = Vendor::withCount('users')
            ->where('is_warehouse', false)
            ->orderBy('name')->get()->map(function ($b) use (
            $salePeriodByBranch, $saleAllByBranch, $prevSaleByBranch, $purchaseByBranch,
            $stockByBranch, $customerByBranch, $supplierByBranch
        ) {
            $sp  = $salePeriodByBranch->get($b->id);
            $sa  = $saleAllByBranch->get($b->id);
            $pp  = $prevSaleByBranch->get($b->id);
            $pur = $purchaseByBranch->get($b->id);
            $stk = $stockByBranch->get($b->id);

            $b->period_revenue  = round($sp->revenue ?? 0, 2);
            $b->period_orders   = $sp->orders ?? 0;
            $b->all_revenue     = round($sa->revenue ?? 0, 2);
            $b->all_orders      = $sa->orders ?? 0;
            $b->customer_due    = round($sa->customer_due ?? 0, 2);
            $prevR = $pp->revenue ?? 0;
            $b->rev_change      = $prevR > 0 ? round((($b->period_revenue - $prevR) / $prevR) * 100, 1) : null;
            $b->total_purchases = round($pur->total_purchases ?? 0, 2);
            $b->supplier_due    = round($pur->supplier_due ?? 0, 2);
            $b->gross_profit    = round($b->all_revenue - $b->total_purchases, 2);
            $b->stock_value     = round($stk->stock_value ?? 0, 2);
            $b->product_count   = $stk->product_count ?? 0;
            $b->out_of_stock    = $stk->out_of_stock ?? 0;
            $b->low_stock       = $stk->low_stock ?? 0;
            $b->customer_count  = $customerByBranch->get($b->id)->count ?? 0;
            $b->supplier_count  = $supplierByBranch->get($b->id)->count ?? 0;

            return $b;
        });

        // ── 30-day chart (always last 30 days) ────────────────────
        $last30 = Sale::withoutGlobalScopes()
            ->completedOrPos()
            ->where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('date')->orderBy('date')->get()->keyBy('date');

        $chartLabels = $chartRevenue = $chartOrders = [];
        for ($i = 29; $i >= 0; $i--) {
            $date           = $now->copy()->subDays($i)->toDateString();
            $chartLabels[]  = Carbon::parse($date)->format('d M');
            $chartRevenue[] = round($last30->get($date)?->revenue ?? 0, 0);
            $chartOrders[]  = $last30->get($date)?->orders ?? 0;
        }

        // ── 6-month trend ──────────────────────────────────────────
        $last6Months = [];
        for ($m = 5; $m >= 0; $m--) {
            $mon = $now->copy()->subMonths($m);
            $last6Months[] = [
                'label'   => $mon->format('M Y'),
                'revenue' => round(Sale::withoutGlobalScopes()->completedOrPos()->whereMonth('created_at',$mon->month)->whereYear('created_at',$mon->year)->sum('total'), 0),
            ];
        }

        // ── Payment breakdown ──────────────────────────────────────
        $paymentBreakdown = Sale::withoutGlobalScopes()
            ->completedOrPos()
            ->selectRaw('payment_method, COUNT(*) as cnt, SUM(total) as rev')
            ->groupBy('payment_method')->orderByDesc('rev')->get();

        // ── Recent sales ───────────────────────────────────────────
        $recentSales = Sale::withoutGlobalScopes()
            ->with(['vendor','customer'])->latest()->take(8)->get();

        $branchColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];

        $stats = [
            'total_branches' => Vendor::count(),
            'total_users'    => User::count(),
            'today_revenue'  => round($todayRev, 2),
            'today_vs_yest'  => $yesterdayRev > 0 ? round((($todayRev - $yesterdayRev) / $yesterdayRev) * 100, 1) : null,
            'all_revenue'    => $allRevenue,
            'all_orders'     => $allOrders,
            'all_due'        => $allDue,
        ];

        // ── Moderator ecosystem & on-duty stats ───────────────────
        $moderatorStats = [
            'total'              => Moderator::count(),
            'active'             => Moderator::where('status', 'active')->count(),
            'on_duty_count'      => ModeratorWorkSession::where('status', 'in_progress')->count(),
            'today_work_seconds' => (int) ModeratorWorkSession::where('status', 'completed')
                                        ->whereDate('started_at', Carbon::today())
                                        ->sum('duration_seconds'),
        ];
        $onDutyModerators = ModeratorWorkSession::where('status', 'in_progress')
            ->with(['moderator', 'logs' => fn($q) => $q->latest('log_time')->take(2)])
            ->latest('started_at')
            ->get();
        $recentWorkLogs = ModeratorWorkLog::with(['moderator', 'workSession'])
            ->latest('log_time')
            ->take(6)
            ->get();

        // ── Reseller ecosystem stats ──────────────────────────────
        $resellerStats = [
            'total'               => Reseller::count(),
            'active'              => Reseller::where('status', 'active')->count(),
            'pending'             => Reseller::where('status', 'pending')->count(),
            'total_orders'        => Sale::withoutGlobalScopes()->whereNotNull('reseller_id')->count(),
            'period_orders'       => Sale::withoutGlobalScopes()->whereNotNull('reseller_id')->whereBetween('created_at', [$fromC, $toC])->count(),
            'total_revenue'       => round(Sale::withoutGlobalScopes()->whereNotNull('reseller_id')->whereIn('order_status', ['completed', 'complete'])->sum('total'), 2),
            'pending_withdrawals' => round(ResellerWithdrawal::sum('amount'), 2),
        ];

        // ── Courier delivery & parcel stats ───────────────────────
        $courierStats = [
            'active_list'   => CourierSetting::where('is_active', true)->get(),
            'active_count'  => CourierSetting::where('is_active', true)->count(),
            'total_parcels' => Sale::withoutGlobalScopes()->whereNotNull('courier_name')->count(),
            'today_parcels' => Sale::withoutGlobalScopes()->whereNotNull('courier_name')->whereDate('courier_sent_at', Carbon::today())->count(),
            'by_courier'    => Sale::withoutGlobalScopes()
                                ->whereNotNull('courier_name')
                                ->selectRaw('courier_name, COUNT(*) as count')
                                ->groupBy('courier_name')
                                ->get(),
        ];

        // ── Inventory & products alert ────────────────────────────
        $inventoryStats = [
            'total_products' => DB::table('products')->count(),
            'out_of_stock'   => DB::table('products')->where('stock_qty', '<=', 0)->count(),
            'low_stock'      => DB::table('products')->where('stock_qty', '>', 0)->where('stock_qty', '<=', 10)->count(),
            'stock_value'    => round(DB::table('products')->sum(DB::raw('stock_qty * cost_price')), 2),
            'total_customers'=> DB::table('customers')->count(),
            'today_orders'   => Sale::withoutGlobalScopes()->whereDate('created_at', Carbon::today())->count(),
        ];

        // ── Compact Executive KPI Summary (All-Time & Period Benchmarks) ──
        $thisMonthStart  = $now->copy()->startOfMonth();
        $thisMonthEnd    = $now->copy()->endOfMonth();
        $lastMonthStart  = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd    = $now->copy()->subMonth()->endOfMonth();

        // 1. Total Sale
        $totalSales       = round(Sale::withoutGlobalScopes()->completedOrPos()->sum('total'), 2);
        $totalSalesOrders = Sale::withoutGlobalScopes()->completedOrPos()->count();
        $allOrdersCount   = Sale::withoutGlobalScopes()->count();
        $totalSalesDue    = round(Sale::withoutGlobalScopes()->completedOrPos()->sum('due_amount'), 2);

        // 2. Today Sale
        $todayOrders      = Sale::withoutGlobalScopes()->whereDate('created_at', $today)->count();

        // 3. Month Sales
        $monthSales       = round(Sale::withoutGlobalScopes()->completedOrPos()->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->sum('total'), 2);
        $monthOrders      = Sale::withoutGlobalScopes()->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count();
        $lastMonthSales   = round(Sale::withoutGlobalScopes()->completedOrPos()->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total'), 2);
        $monthVsLastMonth = $lastMonthSales > 0 ? round((($monthSales - $lastMonthSales) / $lastMonthSales) * 100, 1) : null;

        // 4. Branch Sales (Physical outlets / retail branches)
        $branchSales      = round(Sale::withoutGlobalScopes()->completedOrPos()->whereHas('vendor', fn($q) => $q->where('is_warehouse', false)->where('is_online_store', false))->sum('total'), 2);
        $branchOrders     = Sale::withoutGlobalScopes()->whereHas('vendor', fn($q) => $q->where('is_warehouse', false)->where('is_online_store', false))->count();
        $physicalBranchesCount = Vendor::where('is_warehouse', false)->where('is_online_store', false)->count();
        $allOutletsSales  = round(Sale::withoutGlobalScopes()->completedOrPos()->whereHas('vendor', fn($q) => $q->where('is_warehouse', false))->sum('total'), 2);

        // 5. Total Purchases
        $totalPurchases      = round(Purchase::withoutGlobalScopes()->sum('total'), 2);
        $totalPurchasesDue   = round(Purchase::withoutGlobalScopes()->sum('due_amount'), 2);
        $totalPurchasesCount = Purchase::withoutGlobalScopes()->count();

        // 6. Total Income & Expense (All-Time Accounts)
        $allTimeIncome       = round(IncomeExpense::withoutGlobalScopes()->where('type', 'income')->sum('amount'), 2);
        $allTimeExpense      = round(IncomeExpense::withoutGlobalScopes()->where('type', 'expense')->sum('amount'), 2);

        // 7. Total Profit (P&L)
        $grossProfitAll      = round($totalSales - $totalPurchases, 2);
        $netProfitAll        = round(($totalSales + $allTimeIncome) - ($totalPurchases + $allTimeExpense), 2);

        // 8. Moderators
        $totalModerators     = Moderator::count();
        $activeModerators    = Moderator::where('status', 'active')->count();
        $onDutyModeratorsCount = ModeratorWorkSession::where('status', 'in_progress')->count();

        // 9. Resellers & Reseller Profit
        $totalResellers          = Reseller::count();
        $activeResellers         = Reseller::where('status', 'active')->count();
        $pendingResellers        = Reseller::where('status', 'pending')->count();
        $resellerCompletedProfit = round(Sale::withoutGlobalScopes()->whereNotNull('reseller_id')->whereIn('order_status', ['completed', 'complete'])->sum('reseller_profit'), 2);
        $resellerReturnCharge    = round(Sale::withoutGlobalScopes()->whereNotNull('reseller_id')->whereIn('order_status', ['return', 'returned'])->sum('delivery_charge'), 2);
        $totalResellerProfit     = round(max(0, $resellerCompletedProfit - $resellerReturnCharge), 2);
        $resellerPendingProfit   = round(Sale::withoutGlobalScopes()->whereNotNull('reseller_id')->whereNotIn('order_status', ['completed', 'complete', 'cancelled', 'returned', 'return'])->sum('reseller_profit'), 2);
        $resellerTotalWithdrawn  = round(ResellerWithdrawal::where('status', 'approved')->sum('amount'), 2);

        // 10. Products & Stock
        $totalProducts       = Product::withoutGlobalScopes()->count();
        $outOfStockCount     = Product::withoutGlobalScopes()->where('stock_qty', '<=', 0)->count();
        $lowStockCount       = Product::withoutGlobalScopes()->where('stock_qty', '>', 0)->where('stock_qty', '<=', 10)->count();
        $totalStockQty       = (int) Product::withoutGlobalScopes()->sum('stock_qty');
        $totalStockValue     = round(Product::withoutGlobalScopes()->sum(DB::raw('stock_qty * cost_price')), 2);

        // 11. Categories & Subcategories
        $totalCategories     = Category::count();
        $totalSubcategories  = SubCategory::count();

        $kpi = [
            'total_sales'               => $totalSales,
            'total_sales_orders'        => $totalSalesOrders,
            'all_orders_count'          => $allOrdersCount,
            'total_sales_due'           => $totalSalesDue,
            'today_sales'               => round($todayRev, 2),
            'today_orders'              => $todayOrders,
            'today_change'              => $yesterdayRev > 0 ? round((($todayRev - $yesterdayRev) / $yesterdayRev) * 100, 1) : null,
            'month_sales'               => $monthSales,
            'month_orders'              => $monthOrders,
            'month_change'              => $monthVsLastMonth,
            'branch_sales'              => $branchSales,
            'branch_orders'             => $branchOrders,
            'physical_branches_count'   => $physicalBranchesCount,
            'all_outlets_sales'         => $allOutletsSales,
            'total_profit'              => $netProfitAll,
            'gross_profit'              => $grossProfitAll,
            'total_income'              => $allTimeIncome,
            'total_expense'             => $allTimeExpense,
            'total_purchases'           => $totalPurchases,
            'total_purchases_due'       => $totalPurchasesDue,
            'total_purchases_count'     => $totalPurchasesCount,
            'total_stock_qty'           => $totalStockQty,
            'total_stock_value'         => $totalStockValue,
            'total_products'            => $totalProducts,
            'out_of_stock'              => $outOfStockCount,
            'low_stock'                 => $lowStockCount,
            'total_categories'          => $totalCategories,
            'total_subcategories'       => $totalSubcategories,
            'total_moderators'          => $totalModerators,
            'active_moderators'         => $activeModerators,
            'on_duty_moderators'        => $onDutyModeratorsCount,
            'total_resellers'           => $totalResellers,
            'active_resellers'          => $activeResellers,
            'pending_resellers'         => $pendingResellers,
            'total_reseller_profit'     => $totalResellerProfit,
            'reseller_completed_profit' => $resellerCompletedProfit,
            'reseller_pending_profit'   => $resellerPendingProfit,
            'reseller_withdrawn'        => $resellerTotalWithdrawn,
        ];

        return view('admin.index', compact(
            'kpi',
            'branchList', 'stats', 'statement', 'branchColors',
            'revenue', 'orders', 'revChange', 'prevRev',
            'chartLabels', 'chartRevenue', 'chartOrders',
            'last6Months', 'paymentBreakdown', 'recentSales',
            'moderatorStats', 'onDutyModerators', 'recentWorkLogs',
            'resellerStats', 'courierStats', 'inventoryStats',
            'preset', 'from', 'to'
        ));
    }

    private function resolveRange(Request $request, string $preset): array
    {
        if ($request->filled('from') && $request->filled('to')) {
            return [$request->from, $request->to];
        }
        $now = Carbon::now();
        return match ($preset) {
            'today'      => [today()->toDateString(), today()->toDateString()],
            'yesterday'  => [today()->subDay()->toDateString(), today()->subDay()->toDateString()],
            'this_week'  => [$now->copy()->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()],
            'last_week'  => [$now->copy()->subWeek()->startOfWeek()->toDateString(), $now->copy()->subWeek()->endOfWeek()->toDateString()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth()->toDateString(), $now->copy()->subMonth()->endOfMonth()->toDateString()],
            'this_year'  => [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            'all_time'   => ['2000-01-01', today()->toDateString()],
            default      => [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
        };
    }

    public function storeBranch(Request $request)
    {
        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'owner_name'  => 'required|string|max:255',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|min:6',
            'phone'       => 'nullable|string|max:20',
        ]);

        // is_online_store / is_warehouse are no longer settable from this form — they're
        // rare, one-off flags now managed directly in the database to avoid accidental changes.
        $vendor = Vendor::create([
            'name'            => $request->vendor_name,
            'owner_name'      => $request->owner_name,
            'email'           => $request->email,
            'phone'           => $request->phone,
        ]);

        $user = User::create([
            'name'      => $request->owner_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'vendor_id' => $vendor->id,
        ]);

        $user->assignRole('vendor-owner');
        return back()->with('success', 'Branch created successfully.');
    }

    public function updateBranch(Request $request, Vendor $branch)
    {
        $request->validate(['name' => 'required|string|max:255', 'status' => 'required|in:active,inactive']);

        // is_online_store / is_warehouse are no longer settable from this form — they're
        // rare, one-off flags now managed directly in the database to avoid accidental changes.
        $branch->update($request->only('name', 'owner_name', 'phone', 'address', 'status'));

        return back()->with('success', 'Branch updated.');
    }

    public function destroyBranch(Vendor $branch)
    {
        $branch->delete();
        return back()->with('success', 'Branch deleted.');
    }
}
