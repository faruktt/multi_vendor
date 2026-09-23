<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $reseller = auth('reseller')->user();
        $now      = Carbon::now();

        $orderQ = fn() => Sale::withoutGlobalScopes()->where('reseller_id', $reseller->id);

        // ── Stats ──────────────────────────────────────────────────
        $totalOrders   = $orderQ()->count();
        $totalAmount   = round($orderQ()->sum('total'), 2);
        $totalPaid     = round($orderQ()->sum('paid_amount'), 2);
        $totalDue      = round($orderQ()->sum('due_amount'), 2);

        $thisMonthOrders = $orderQ()->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $thisMonthAmount = round($orderQ()->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('total'), 2);

        // ── Recent orders ──────────────────────────────────────────
        $recentOrders = $orderQ()->with(['saleItems.product'])->latest()->take(10)->get();

        // ── 6-month chart ──────────────────────────────────────────
        $chartLabels = $chartRevenue = $chartOrders = [];
        for ($m = 5; $m >= 0; $m--) {
            $mon = $now->copy()->subMonths($m);
            $chartLabels[]  = $mon->format('M Y');
            $chartRevenue[] = round($orderQ()->whereMonth('created_at', $mon->month)->whereYear('created_at', $mon->year)->sum('total'), 0);
            $chartOrders[]  = $orderQ()->whereMonth('created_at', $mon->month)->whereYear('created_at', $mon->year)->count();
        }

        // ── Order status breakdown ─────────────────────────────────
        $statusBreakdown = $orderQ()
            ->selectRaw('order_status, COUNT(*) as cnt, SUM(total) as rev')
            ->groupBy('order_status')
            ->get();

        return view('reseller.dashboard', compact(
            'reseller',
            'totalOrders', 'totalAmount', 'totalPaid', 'totalDue',
            'thisMonthOrders', 'thisMonthAmount',
            'recentOrders',
            'chartLabels', 'chartRevenue', 'chartOrders',
            'statusBreakdown'
        ));
    }
}
