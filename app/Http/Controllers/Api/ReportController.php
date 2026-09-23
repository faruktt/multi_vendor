<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function salesSummary(Request $request)
    {
        $range = $request->get('range', 'monthly');

        // VendorScope handles vendor filtering (and bypasses for super-admin)
        $query = Sale::query();

        if ($range === 'daily') {
            $sales   = $query->whereDate('created_at', today())->get();
            $grouped = $sales->groupBy(fn($s) => $s->created_at->format('H:00'));
        } elseif ($range === 'weekly') {
            $sales   = $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->get();
            $grouped = $sales->groupBy(fn($s) => $s->created_at->format('D'));
        } else {
            $sales   = $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->get();
            $grouped = $sales->groupBy(fn($s) => $s->created_at->format('d M'));
        }

        $data = $grouped->map(fn($group, $label) => [
            'label'   => $label,
            'revenue' => round($group->sum('total'), 2),
            'orders'  => $group->count(),
        ])->values();

        return response()->json($data);
    }

    public function topProducts(Request $request)
    {
        $limit    = $request->get('limit', 8);
        $user     = $request->user();
        $vendorId = $user->vendor_id;

        // SaleItem has no VendorScope, so we join sales and filter manually.
        // Super-admin (vendor_id = null) sees all vendors combined.
        $query = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->selectRaw('products.name, SUM(sale_items.quantity) as total_sold, SUM(sale_items.subtotal) as revenue')
            ->groupBy('sale_items.product_id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit($limit);

        if (!$user->hasRole('super-admin')) {
            $query->where('sales.vendor_id', $vendorId);
        }

        return response()->json($query->get());
    }

    public function paymentStatus(Request $request)
    {
        // VendorScope handles filtering
        $data = Sale::selectRaw('payment_status as name, COUNT(*) as value')
            ->groupBy('payment_status')
            ->get()
            ->map(fn($r) => [
                'name'  => ucfirst($r->name),
                'value' => (int) $r->value,
            ]);

        return response()->json($data);
    }

    public function overview(Request $request)
    {
        // VendorScope handles vendor filtering for all Sale:: queries below
        $lastMonthDate = now()->subMonth();

        $thisMonth = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        $lastMonth = Sale::whereMonth('created_at', $lastMonthDate->month)
            ->whereYear('created_at', $lastMonthDate->year);

        return response()->json([
            'this_month_revenue' => round((clone $thisMonth)->sum('total'), 2),
            'this_month_orders'  => (clone $thisMonth)->count(),
            'last_month_revenue' => round((clone $lastMonth)->sum('total'), 2),
            'last_month_orders'  => (clone $lastMonth)->count(),
            'total_revenue'      => round(Sale::sum('total'), 2),
            'total_orders'       => Sale::count(),
            'total_due'          => round(Sale::sum('due_amount'), 2),
        ]);
    }
}
