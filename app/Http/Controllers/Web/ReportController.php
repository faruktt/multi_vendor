<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\IncomeExpense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request, Vendor $branch)
    {
        // ── Date range ────────────────────────────────────────────
        $preset = $request->get('preset', 'this_month');
        [$from, $to] = $this->resolveRange($request, $preset);

        $fromCarbon = Carbon::parse($from)->startOfDay();
        $toCarbon   = Carbon::parse($to)->endOfDay();

        // ── Previous period (for % change) ───────────────────────
        $diffDays   = $fromCarbon->diffInDays($toCarbon) + 1;
        $prevFrom   = $fromCarbon->copy()->subDays($diffDays)->startOfDay();
        $prevTo     = $fromCarbon->copy()->subDay()->endOfDay();

        // ── Sales ─────────────────────────────────────────────────
        $saleQ    = fn() => Sale::withoutGlobalScopes()->where('vendor_id', $branch->id);
        $salePeriod = (clone $saleQ())->whereBetween('created_at', [$fromCarbon, $toCarbon]);
        $salePrev   = (clone $saleQ())->whereBetween('created_at', [$prevFrom, $prevTo]);

        $sales = [
            'revenue'       => round($salePeriod->sum('total'), 2),
            'orders'        => $salePeriod->count(),
            'paid'          => round($salePeriod->sum('paid_amount'), 2),
            'due_period'    => round($salePeriod->sum('due_amount'), 2),
            'prev_revenue'  => round($salePrev->sum('total'), 2),
            'prev_orders'   => $salePrev->count(),
            'total_revenue' => round($saleQ()->sum('total'), 2),
            'total_orders'  => $saleQ()->count(),
            'total_due'     => round($saleQ()->sum('due_amount'), 2),
            'avg_order'     => $salePeriod->count() > 0
                ? round($salePeriod->sum('total') / $salePeriod->count(), 2) : 0,
        ];

        // ── Purchases ─────────────────────────────────────────────
        $purQ       = fn() => Purchase::withoutGlobalScopes()->where('vendor_id', $branch->id);
        $purPeriod  = (clone $purQ())->whereBetween('created_at', [$fromCarbon, $toCarbon]);

        $purchases = [
            'total_period'  => round($purPeriod->sum('total'), 2),
            'orders_period' => $purPeriod->count(),
            'total_all'     => round($purQ()->sum('total'), 2),
            'due_all'       => round($purQ()->sum('due_amount'), 2),
        ];

        // ── Gross profit (revenue - cost of goods in period) ─────
        $grossProfit = round($sales['revenue'] - $purchases['total_period'], 2);

        // ── Inventory ─────────────────────────────────────────────
        $prodQ = $branch->products();
        $inventory = [
            'total'       => (clone $prodQ)->count(),
            'out_of_stock'=> (clone $prodQ)->where('stock_qty', '<=', 0)->count(),
            'low_stock'   => (clone $prodQ)->where('stock_qty', '>', 0)->where('stock_qty', '<=', 10)->count(),
            'stock_value' => round((clone $prodQ)->selectRaw('SUM(stock_qty * cost_price) as v')->value('v') ?? 0, 2),
        ];

        // ── Customers ─────────────────────────────────────────────
        $customers = [
            'total'    => $branch->customers()->count(),
            'new'      => $branch->customers()->whereBetween('created_at', [$fromCarbon, $toCarbon])->count(),
            'with_due' => Customer::withoutGlobalScopes()
                ->where('vendor_id', $branch->id)
                ->whereHas('sales', fn($q) => $q->withoutGlobalScopes()->where('due_amount', '>', 0))
                ->count(),
        ];

        // ── Suppliers ─────────────────────────────────────────────
        $suppliersDue = $branch->suppliers()->count();

        // ── Finance ───────────────────────────────────────────────
        $finQ = fn() => IncomeExpense::withoutGlobalScopes()->where('vendor_id', $branch->id);
        $finance = [
            'income'  => round($finQ()->where('type', 'income')->whereBetween('date', [$from, $to])->sum('amount'), 2),
            'expense' => round($finQ()->where('type', 'expense')->whereBetween('date', [$from, $to])->sum('amount'), 2),
        ];
        $finance['net'] = round($finance['income'] - $finance['expense'], 2);

        // ── Top Products ──────────────────────────────────────────
        $topProducts = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.vendor_id', $branch->id)
            ->whereBetween('sales.created_at', [$fromCarbon, $toCarbon])
            ->selectRaw('products.name, SUM(sale_items.quantity) as total_sold, SUM(sale_items.subtotal) as revenue')
            ->groupBy('sale_items.product_id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // ── Payment status breakdown ──────────────────────────────
        $paymentStatus = (clone $saleQ())
            ->whereBetween('created_at', [$fromCarbon, $toCarbon])
            ->selectRaw('payment_status as name, COUNT(*) as value')
            ->groupBy('payment_status')
            ->get()
            ->map(fn($r) => ['name' => ucfirst($r->name), 'value' => (int) $r->value]);

        // ── Payment method breakdown ──────────────────────────────
        $paymentMethods = (clone $saleQ())
            ->whereBetween('created_at', [$fromCarbon, $toCarbon])
            ->selectRaw('payment_method as name, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();

        // ── Recent Sales ──────────────────────────────────────────
        $recentSales = (clone $saleQ())
            ->with('customer')
            ->whereBetween('created_at', [$fromCarbon, $toCarbon])
            ->latest()
            ->limit(10)
            ->get();

        // ── Sales chart ───────────────────────────────────────────
        $salesChart = $this->buildChart($branch, $fromCarbon, $toCarbon);

        return view('reports.index', compact(
            'branch', 'sales', 'purchases', 'grossProfit', 'inventory',
            'customers', 'suppliersDue', 'finance',
            'topProducts', 'paymentStatus', 'paymentMethods', 'recentSales',
            'salesChart', 'preset', 'from', 'to'
        ));
    }

    private function resolveRange(Request $request, string $preset): array
    {
        if ($request->filled('from') && $request->filled('to')) {
            return [$request->from, $request->to];
        }
        return match ($preset) {
            'today'      => [today()->toDateString(), today()->toDateString()],
            'yesterday'  => [today()->subDay()->toDateString(), today()->subDay()->toDateString()],
            'this_week'  => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'last_week'  => [now()->subWeek()->startOfWeek()->toDateString(), now()->subWeek()->endOfWeek()->toDateString()],
            'last_month' => [now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
            'this_year'  => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'all_time'   => ['2000-01-01', today()->toDateString()],
            default      => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()], // this_month
        };
    }

    private function buildChart(Vendor $branch, Carbon $from, Carbon $to): array
    {
        $diffDays = $from->diffInDays($to);

        $sales = Sale::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        if ($diffDays <= 1) {
            $grouped = $sales->groupBy(fn($s) => $s->created_at->format('H:00'));
        } elseif ($diffDays <= 31) {
            $grouped = $sales->groupBy(fn($s) => $s->created_at->format('d M'));
        } elseif ($diffDays <= 92) {
            $grouped = $sales->groupBy(fn($s) => 'W' . $s->created_at->format('W'));
        } else {
            $grouped = $sales->groupBy(fn($s) => $s->created_at->format('M Y'));
        }

        return $grouped->map(fn($g, $label) => [
            'label'   => $label,
            'revenue' => round($g->sum('total'), 2),
            'orders'  => $g->count(),
        ])->values()->all();
    }
}
