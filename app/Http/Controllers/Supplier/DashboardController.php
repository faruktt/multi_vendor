<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $supplier = auth('supplier')->user();

        // Product stats
        $totalProducts = Product::withoutGlobalScopes()
            ->where('supplier_id', $supplier->id)
            ->count();

        $activeProducts = Product::withoutGlobalScopes()
            ->where('supplier_id', $supplier->id)
            ->where('status', 'active')
            ->count();

        $outOfStock = Product::withoutGlobalScopes()
            ->where('supplier_id', $supplier->id)
            ->where('stock_qty', '<=', 0)
            ->count();

        // Sales / Order items stats
        $saleItemsQuery = SaleItem::where('supplier_id', $supplier->id);

        $totalItemsSold       = (int) $saleItemsQuery->sum('quantity');
        $totalSalesAmount     = (float) $saleItemsQuery->sum('subtotal');
        $totalAdminCommission = (float) $saleItemsQuery->sum('admin_commission_amount');
        $totalNetEarnings     = (float) $saleItemsQuery->sum('supplier_earning');

        $thisMonthAmount = (float) SaleItem::where('supplier_id', $supplier->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('subtotal');

        // Total distinct orders containing this supplier's items
        $totalOrders = Sale::withoutGlobalScopes()
            ->whereHas('saleItems', fn($q) => $q->where('supplier_id', $supplier->id))
            ->count();

        // Recent Orders with items for this supplier
        $recentOrders = Sale::withoutGlobalScopes()
            ->whereHas('saleItems', fn($q) => $q->where('supplier_id', $supplier->id))
            ->with([
                'customer',
                'saleItems' => fn($q) => $q->where('supplier_id', $supplier->id)->with('product', 'variant')
            ])
            ->latest()
            ->take(5)
            ->get();

        // Recent Products
        $recentProducts = Product::withoutGlobalScopes()
            ->where('supplier_id', $supplier->id)
            ->with('category')
            ->latest()
            ->take(5)
            ->get();

        $totalWithdrawn      = $supplier->totalWithdrawnAmount();
        $pendingPayout       = $supplier->pendingWithdrawnAmount();
        $availableBalance    = $supplier->availableBalance();
        $withdrawableBalance = $supplier->withdrawableBalance();

        return view('supplier.dashboard', compact(
            'supplier',
            'totalProducts',
            'activeProducts',
            'outOfStock',
            'totalItemsSold',
            'totalSalesAmount',
            'totalAdminCommission',
            'totalNetEarnings',
            'totalWithdrawn',
            'pendingPayout',
            'availableBalance',
            'withdrawableBalance',
            'thisMonthAmount',
            'totalOrders',
            'recentOrders',
            'recentProducts'
        ));
    }
}
