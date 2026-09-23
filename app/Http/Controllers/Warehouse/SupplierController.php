<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Vendor;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request, Vendor $branch)
    {
        $query = $branch->suppliers()
            ->withCount('purchases')
            ->withSum('purchases', 'total')
            ->withSum('purchases', 'paid_amount')
            ->withSum('purchases', 'due_amount')
            ->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $suppliers = $query->paginate(20)->withQueryString();

        $totals = [
            'suppliers' => $branch->suppliers()->count(),
            'orders'    => Purchase::withoutGlobalScopes()->where('vendor_id', $branch->id)->whereNotNull('supplier_id')->count(),
            'spent'     => Purchase::withoutGlobalScopes()->where('vendor_id', $branch->id)->whereNotNull('supplier_id')->sum('total'),
            'due'       => Purchase::withoutGlobalScopes()->where('vendor_id', $branch->id)->whereNotNull('supplier_id')->sum('due_amount'),
        ];

        return view('warehouse.suppliers.index', compact('branch', 'suppliers', 'totals'));
    }

    public function store(Request $request, Vendor $branch)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'note'    => 'nullable|string|max:500',
        ]);

        $branch->suppliers()->create($request->only('name', 'phone', 'email', 'address'));

        return back()->with('success', 'Supplier added successfully.');
    }

    public function update(Request $request, Vendor $branch, Supplier $supplier)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier->update($request->only('name', 'phone', 'email', 'address'));

        return back()->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Vendor $branch, Supplier $supplier)
    {
        $supplier->delete();
        return back()->with('success', 'Supplier deleted.');
    }

    public function report(Vendor $branch, Supplier $supplier)
    {
        $purchases = Purchase::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('supplier_id', $supplier->id)
            ->with('items.product')
            ->latest()
            ->get();

        $stats = [
            'total_orders'  => $purchases->count(),
            'total_bought'  => round($purchases->sum('total'), 2),
            'total_paid'    => round($purchases->sum('paid_amount'), 2),
            'total_due'     => round($purchases->sum('due_amount'), 2),
            'avg_order'     => $purchases->count() > 0 ? round($purchases->sum('total') / $purchases->count(), 2) : 0,
        ];

        // Top products purchased from this supplier
        $topProducts = $purchases->flatMap->items
            ->groupBy('product_id')
            ->map(function ($items) {
                return [
                    'name'     => $items->first()->product_name,
                    'quantity' => $items->sum('quantity'),
                    'total'    => $items->sum('subtotal'),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        // Payment method breakdown
        $paymentBreakdown = $purchases->groupBy('payment_method')
            ->map(fn($g) => ['count' => $g->count(), 'total' => $g->sum('total')])
            ->sortByDesc('total');

        return view('warehouse.suppliers.report', compact(
            'branch', 'supplier', 'purchases', 'stats', 'topProducts', 'paymentBreakdown'
        ));
    }
}
