<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request, Vendor $branch)
    {
        $productsQ = $branch->products();
        $summary = [
            'total_products'    => (clone $productsQ)->count(),
            'out_of_stock'      => (clone $productsQ)->where('stock_qty', '<=', 0)->count(),
            'low_stock'         => (clone $productsQ)->where('stock_qty', '>', 0)->where('stock_qty', '<=', 10)->count(),
            'total_stock_value' => (clone $productsQ)->selectRaw('SUM(stock_qty * cost_price) as val')->value('val') ?? 0,
        ];

        $productQuery = $branch->products()->with('category')->orderBy('stock_qty');
        if ($request->filled('search')) {
            $productQuery->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('stock_filter')) {
            match ($request->stock_filter) {
                'out'  => $productQuery->where('stock_qty', '<=', 0),
                'low'  => $productQuery->where('stock_qty', '>', 0)->where('stock_qty', '<=', 10),
                'ok'   => $productQuery->where('stock_qty', '>', 10),
                default => null,
            };
        }
        $products = $productQuery->paginate(20, ['*'], 'ppage')->withQueryString();

        Product::attachStockMetrics($products, $branch->id);

        $movQuery = StockMovement::withoutGlobalScopes()
            ->with('product')
            ->where('vendor_id', $branch->id)
            ->latest();
        if ($request->filled('mtype')) {
            $movQuery->where('type', $request->mtype);
        }
        if ($request->filled('ref_type')) {
            $movQuery->where('reference_type', $request->ref_type);
        }
        if ($request->filled('from')) {
            $movQuery->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $movQuery->whereDate('created_at', '<=', $request->to);
        }
        $movements = $movQuery->paginate(15, ['*'], 'mpage')->withQueryString();

        return view('warehouse.stock.index', compact('branch', 'products', 'summary', 'movements'));
    }
}
