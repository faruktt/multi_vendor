<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->get()->map(fn($p) => [
            'id'            => $p->id,
            'name'          => $p->name,
            'sku'           => $p->sku,
            'category'      => $p->category?->name ?? '—',
            'stock_qty'     => $p->stock_qty,
            'price'         => $p->price,
            'status'        => $p->stock_qty <= 0 ? 'out' : ($p->stock_qty <= 5 ? 'low' : 'ok'),
        ]);

        return response()->json($products);
    }

    public function movements(Request $request)
    {
        $productId = $request->query('product_id');

        $q = StockMovement::with('product')->latest()->limit(50);
        if ($productId) {
            $q->where('product_id', $productId);
        }

        return response()->json($q->get()->map(fn($m) => [
            'id'             => $m->id,
            'product'        => $m->product?->name ?? '—',
            'type'           => $m->type,
            'quantity'       => $m->quantity,
            'reference_type' => $m->reference_type,
            'note'           => $m->note,
            'date'           => $m->created_at->format('d M Y, h:i A'),
        ]));
    }

    public function summary()
    {
        return response()->json([
            'total_products'   => Product::count(),
            'out_of_stock'     => Product::where('stock_qty', '<=', 0)->count(),
            'low_stock'        => Product::where('stock_qty', '>', 0)->where('stock_qty', '<=', 5)->count(),
            'total_stock_value'=> Product::selectRaw('SUM(stock_qty * price) as val')->value('val') ?? 0,
            'recent_in'        => StockMovement::where('type', 'in')->whereDate('created_at', today())->sum('quantity'),
            'recent_out'       => StockMovement::where('type', 'out')->whereDate('created_at', today())->sum('quantity'),
        ]);
    }
}
