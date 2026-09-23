<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index(Request $request, Vendor $branch)
    {
        $query = $branch->customers()
            ->withCount('sales')
            ->withSum('sales', 'total')
            ->withSum('sales', 'paid_amount')
            ->withSum('sales', 'due_amount')
            ->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        $totals = [
            'customers' => $branch->customers()->count(),
            'orders'    => Sale::withoutGlobalScopes()->where('vendor_id', $branch->id)->whereNotNull('customer_id')->count(),
            'revenue'   => Sale::withoutGlobalScopes()->where('vendor_id', $branch->id)->whereNotNull('customer_id')->sum('total'),
            'due'       => Sale::withoutGlobalScopes()->where('vendor_id', $branch->id)->whereNotNull('customer_id')->sum('due_amount'),
        ];

        return view('customers.index', compact('branch', 'customers', 'totals'));
    }

    public function store(Request $request, Vendor $branch)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'image'   => 'nullable|image|max:2048',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('customers', 'uploads')
            : null;

        $branch->customers()->create([
            'name'    => $request->name,
            'phone'   => $request->phone,
            'email'   => $request->email,
            'address' => $request->address,
            'image'   => $imagePath,
        ]);

        return back()->with('success', 'Customer added.');
    }

    public function update(Request $request, Vendor $branch, Customer $customer)
    {
        $request->validate([
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
            'name'    => $request->name,
            'phone'   => $request->phone,
            'email'   => $request->email,
            'address' => $request->address,
            'image'   => $imagePath,
        ]);

        return back()->with('success', 'Customer updated.');
    }

    public function destroy(Vendor $branch, Customer $customer)
    {
        if ($customer->image) Storage::disk('uploads')->delete($customer->image);
        $customer->delete();
        return back()->with('success', 'Customer deleted.');
    }

    public function report(Vendor $branch, Customer $customer)
    {
        $sales = Sale::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('customer_id', $customer->id)
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

        $stats = [
            'total_orders' => $sales->count(),
            'total_spent'  => round($sales->sum('total'), 2),
            'total_paid'   => round($sales->sum('paid_amount'), 2),
            'total_due'    => round($sales->sum('due_amount'), 2),
            'avg_order'    => $sales->count() > 0 ? round($sales->sum('total') / $sales->count(), 2) : 0,
        ];

        $paymentBreakdown = $sales->groupBy('payment_method')
            ->map(fn($g) => ['count' => $g->count(), 'total' => $g->sum('total')])
            ->sortByDesc('total');

        return view('customers.report', compact(
            'branch', 'customer', 'sales', 'topProducts', 'stats', 'paymentBreakdown'
        ));
    }
}
