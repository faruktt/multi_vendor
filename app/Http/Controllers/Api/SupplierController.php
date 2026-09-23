<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('purchases')
            ->withSum(['purchases' => fn($q) => $q->withoutGlobalScopes()], 'total')
            ->withSum(['purchases' => fn($q) => $q->withoutGlobalScopes()], 'due_amount')
            ->latest()
            ->get();

        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $vendorId = $request->user()->vendor_id;

        if (!$vendorId) {
            return response()->json(['message' => 'Super-admin must act on behalf of a vendor. Use vendor panel.'], 422);
        }

        $supplier = Supplier::create([
            'vendor_id' => $vendorId,
            ...$v,
        ]);

        return response()->json($supplier->loadCount('purchases'), 201);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $v = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier->update($v);
        return response()->json($supplier->loadCount('purchases'));
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return response()->json(['message' => 'Supplier deleted']);
    }

    public function report(Supplier $supplier)
    {
        $purchases = Purchase::withoutGlobalScopes()
            ->where('supplier_id', $supplier->id)
            ->with('items.product')
            ->latest()
            ->get();

        return response()->json([
            'supplier'        => $supplier,
            'total_purchases' => $purchases->count(),
            'total_value'     => round($purchases->sum('total'), 2),
            'total_paid'      => round($purchases->sum('paid_amount'), 2),
            'total_due'       => round($purchases->sum('due_amount'), 2),
            'recent'          => $purchases->take(10)->map(fn($p) => [
                'id'             => $p->id,
                'invoice_no'     => $p->invoice_no,
                'total'          => $p->total,
                'paid_amount'    => $p->paid_amount,
                'due_amount'     => $p->due_amount,
                'payment_status' => $p->payment_status,
                'items_count'    => $p->items->count(),
                'date'           => $p->created_at->format('d M Y'),
            ]),
        ]);
    }
}
