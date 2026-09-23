<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /** List all customer sales containing products from this supplier */
    public function index(Request $request)
    {
        $supplier = auth('supplier')->user();

        $query = Sale::withoutGlobalScopes()
            ->whereHas('saleItems', fn($q) => $q->where('supplier_id', $supplier->id))
            ->with([
                'customer',
                'saleItems' => fn($q) => $q->where('supplier_id', $supplier->id)->with('product', 'variant')
            ])
            ->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_no', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($cq) use ($s) {
                      $cq->where('name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query->paginate(20)->withQueryString();

        // Calculate summary stats
        $totalOrdersCount     = Sale::withoutGlobalScopes()
            ->whereHas('saleItems', fn($q) => $q->where('supplier_id', $supplier->id))
            ->count();

        $totalGrossSales      = (float) \App\Models\SaleItem::where('supplier_id', $supplier->id)->sum('subtotal');
        $totalAdminCommission = (float) \App\Models\SaleItem::where('supplier_id', $supplier->id)->sum('admin_commission_amount');
        $totalNetEarnings     = (float) \App\Models\SaleItem::where('supplier_id', $supplier->id)->sum('supplier_earning');

        return view('supplier.orders.index', compact(
            'orders',
            'supplier',
            'totalOrdersCount',
            'totalGrossSales',
            'totalAdminCommission',
            'totalNetEarnings'
        ));
    }

    /** View detailed invoice for items sold by this supplier */
    public function show($id)
    {
        $supplier = auth('supplier')->user();

        $sale = Sale::withoutGlobalScopes()
            ->where('id', $id)
            ->whereHas('saleItems', fn($q) => $q->where('supplier_id', $supplier->id))
            ->with([
                'customer',
                'saleItems' => fn($q) => $q->where('supplier_id', $supplier->id)->with('product', 'variant')
            ])
            ->firstOrFail();

        $supplierItems      = $sale->saleItems;
        $supplierGross      = $supplierItems->sum('subtotal');
        $supplierCommission = $supplierItems->sum('admin_commission_amount');
        $supplierNet        = $supplierItems->sum('supplier_earning');
        $supplierQty        = $supplierItems->sum('quantity');

        return view('supplier.orders.show', compact(
            'sale',
            'supplier',
            'supplierItems',
            'supplierGross',
            'supplierCommission',
            'supplierNet',
            'supplierQty'
        ));
    }

    /** Print A4 invoice sheet for this supplier's order */
    public function invoice($id)
    {
        $supplier = auth('supplier')->user();

        $sale = Sale::withoutGlobalScopes()
            ->where('id', $id)
            ->whereHas('saleItems', fn($q) => $q->where('supplier_id', $supplier->id))
            ->with([
                'customer',
                'vendor',
                'saleItems' => fn($q) => $q->where('supplier_id', $supplier->id)->with('product', 'variant')
            ])
            ->firstOrFail();

        $supplierItems      = $sale->saleItems;
        $supplierGross      = $supplierItems->sum('subtotal');
        $supplierCommission = $supplierItems->sum('admin_commission_amount');
        $supplierNet        = $supplierItems->sum('supplier_earning');
        $supplierQty        = $supplierItems->sum('quantity');

        return view('supplier.orders.invoice', compact(
            'sale',
            'supplier',
            'supplierItems',
            'supplierGross',
            'supplierCommission',
            'supplierNet',
            'supplierQty'
        ));
    }
}
