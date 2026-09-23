<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ResellerWithdrawal;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display reseller's comprehensive "My Account" financial overview
     */
    public function index(Request $request)
    {
        $reseller = auth('reseller')->user();
        $now = Carbon::now();

        $orderQ = fn() => Sale::withoutGlobalScopes()->where('reseller_id', $reseller->id);

        // Core Financial Metrics
        $totalOrders      = $orderQ()->count();
        $totalSalesAmount = round((float) $orderQ()->sum('total'), 2);
        $totalPaidAmount  = round((float) $orderQ()->sum('paid_amount'), 2);
        $totalDueAmount   = round((float) $orderQ()->sum('due_amount'), 2);

        // Profits
        $totalProfit          = $reseller->total_profit; // completed profit
        $pendingProfit        = $reseller->pending_profit; // in-transit / awaiting completion
        $totalPotentialProfit = round($totalProfit + $pendingProfit, 2);

        // Withdrawals & Balances
        $totalWithdrawn      = $reseller->total_withdrawn;
        $availableBalance    = $reseller->available_balance;
        $pendingWithdrawals  = $reseller->pending_withdrawals;
        $withdrawableBalance = $reseller->withdrawable_balance;

        // This Month Performance
        $thisMonthOrders = $orderQ()
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        $thisMonthSales = round((float) $orderQ()
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('total'), 2);

        $thisMonthProfit = round((float) $orderQ()
            ->whereIn('order_status', ['completed', 'complete'])
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('reseller_profit'), 2);

        // Order Status Counts & breakdown
        $statusCounts = $orderQ()
            ->selectRaw('order_status, COUNT(*) as count, SUM(total) as total_amount, SUM(reseller_profit) as total_profit')
            ->groupBy('order_status')
            ->get()
            ->keyBy('order_status');

        $completedOrdersCount  = ($statusCounts->get('completed')?->count ?? 0) + ($statusCounts->get('complete')?->count ?? 0);
        $deliveredOrdersCount  = $statusCounts->get('delivered')?->count ?? 0;
        $pendingOrdersCount    = $statusCounts->get('pending')?->count ?? 0;
        $processingOrdersCount = ($statusCounts->get('processing')?->count ?? 0)
                               + ($statusCounts->get('sent_to_courier')?->count ?? 0)
                               + ($statusCounts->get('out_for_delivery')?->count ?? 0);
        $cancelledOrdersCount  = $statusCounts->get('cancelled')?->count ?? 0;

        // Average profit per completed order
        $avgProfitPerOrder = $completedOrdersCount > 0 ? round($totalProfit / $completedOrdersCount, 2) : 0;

        // Recent Orders with profit details
        $recentOrders = $orderQ()
            ->with(['customer', 'saleItems.product'])
            ->latest()
            ->take(10)
            ->get();

        // Recent Withdrawals
        $recentWithdrawals = $reseller->withdrawals()
            ->with('processedBy')
            ->latest()
            ->take(10)
            ->get();

        return view('reseller.account.index', compact(
            'reseller',
            'totalOrders',
            'totalSalesAmount',
            'totalPaidAmount',
            'totalDueAmount',
            'totalProfit',
            'pendingProfit',
            'totalPotentialProfit',
            'totalWithdrawn',
            'availableBalance',
            'pendingWithdrawals',
            'withdrawableBalance',
            'thisMonthOrders',
            'thisMonthSales',
            'thisMonthProfit',
            'statusCounts',
            'completedOrdersCount',
            'deliveredOrdersCount',
            'pendingOrdersCount',
            'processingOrdersCount',
            'cancelledOrdersCount',
            'avgProfitPerOrder',
            'recentOrders',
            'recentWithdrawals'
        ));
    }
}
