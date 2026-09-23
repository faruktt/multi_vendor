<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index()
    {
        return response()->json(Customer::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'image'   => 'nullable|image|max:2048',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('customers', 'uploads')
            : null;

        $customer = Customer::create([
            'vendor_id' => $request->user()->vendor_id,
            'name'      => $validated['name'],
            'phone'     => $validated['phone'] ?? null,
            'email'     => $validated['email'] ?? null,
            'address'   => $validated['address'] ?? null,
            'image'     => $imagePath,
        ]);

        return response()->json($customer, 201);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'image'   => 'nullable|image|max:2048',
        ]);

        $imagePath = $customer->image;
        if ($request->hasFile('image')) {
            if ($customer->image) Storage::disk('uploads')->delete($customer->image);
            $imagePath = $request->file('image')->store('customers', 'uploads');
        }

        $customer->update([
            'name'    => $validated['name'],
            'phone'   => $validated['phone'] ?? $customer->phone,
            'email'   => $validated['email'] ?? $customer->email,
            'address' => $validated['address'] ?? $customer->address,
            'image'   => $imagePath,
        ]);

        return response()->json($customer);
    }

    public function destroy(Customer $customer)
    {
        if ($customer->image) Storage::disk('uploads')->delete($customer->image);
        $customer->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function report(Customer $customer)
    {
        $sales = Sale::where('customer_id', $customer->id)
            ->with('saleItems.product')
            ->latest()
            ->get();

        $saleIds = $sales->pluck('id');

        $topProducts = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereIn('sale_id', $saleIds)
            ->selectRaw('products.name, SUM(sale_items.quantity) as total_qty, SUM(sale_items.subtotal) as total_revenue')
            ->groupBy('sale_items.product_id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return response()->json([
            'customer'     => $customer,
            'total_orders' => $sales->count(),
            'total_spent'  => round($sales->sum('total'), 2),
            'total_paid'   => round($sales->sum('paid_amount'), 2),
            'total_due'    => round($sales->sum('due_amount'), 2),
            'recent_sales' => $sales->take(10)->map(fn($s) => [
                'id'             => $s->id,
                'invoice_no'     => $s->invoice_no,
                'total'          => $s->total,
                'paid_amount'    => $s->paid_amount,
                'payment_status' => $s->payment_status,
                'items_count'    => $s->saleItems->count(),
                'date'           => $s->created_at->format('d M Y'),
            ]),
            'top_products' => $topProducts,
        ]);
    }
}
