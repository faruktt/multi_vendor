<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_vendors'  => Vendor::count(),
            'total_users'    => User::count(),
            'total_sales'    => Sale::withoutGlobalScopes()->count(),
            'total_revenue'  => Sale::withoutGlobalScopes()->sum('total'),
            'today_revenue'  => Sale::withoutGlobalScopes()->whereDate('created_at', today())->sum('total'),
            'today_sales'    => Sale::withoutGlobalScopes()->whereDate('created_at', today())->count(),
        ]);
    }

    public function vendorIndex()
    {
        $vendors = Vendor::withCount('users')
            ->withSum(['sales' => fn($q) => $q->withoutGlobalScopes()], 'total')
            ->withCount(['sales' => fn($q) => $q->withoutGlobalScopes()])
            ->latest()
            ->get();

        return response()->json($vendors);
    }

    public function vendorStore(Request $request)
    {
        $validated = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'owner_name'  => 'required|string|max:255',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|min:6',
            'phone'       => 'nullable|string',
            'address'     => 'nullable|string',
        ]);

        $vendor = Vendor::create([
            'name'       => $validated['vendor_name'],
            'owner_name' => $validated['owner_name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? null,
            'address'    => $validated['address'] ?? null,
        ]);

        $user = User::create([
            'name'      => $validated['owner_name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'vendor_id' => $vendor->id,
        ]);
        $user->assignRole('vendor-owner');

        return response()->json([
            'vendor' => $vendor->loadCount('users'),
            'user'   => array_merge($user->toArray(), ['roles' => $user->getRoleNames()]),
        ], 201);
    }

    public function vendorUpdate(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'owner_name' => 'nullable|string',
            'phone'      => 'nullable|string',
            'address'    => 'nullable|string',
            'status'     => 'nullable|in:active,inactive',
        ]);

        $vendor->update($validated);
        return response()->json($vendor->loadCount('users'));
    }

    public function vendorDestroy(Vendor $vendor)
    {
        if ($vendor->id === 1) {
            return response()->json(['message' => 'Default vendor cannot be deleted'], 403);
        }

        $vendor->users()->delete();
        $vendor->delete();
        return response()->json(['message' => 'Vendor and all users deleted']);
    }

    public function vendorReport(Vendor $vendor)
    {
        $vid = $vendor->id;

        // ── Stats ────────────────────────────────────────────
        $salesQ = Sale::withoutGlobalScopes()->where('vendor_id', $vid);

        $stats = [
            'total_revenue'   => round((clone $salesQ)->sum('total'), 2),
            'total_orders'    => (clone $salesQ)->count(),
            'total_due'       => round((clone $salesQ)->sum('due_amount'), 2),
            'total_paid'      => round((clone $salesQ)->sum('paid_amount'), 2),
            'total_products'  => Product::withoutGlobalScopes()->where('vendor_id', $vid)->count(),
            'total_customers' => Customer::withoutGlobalScopes()->where('vendor_id', $vid)->count(),
        ];

        // ── Last 6 months revenue ────────────────────────────
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m   = now()->subMonths($i);
            $rev = Sale::withoutGlobalScopes()
                ->where('vendor_id', $vid)
                ->whereMonth('created_at', $m->month)
                ->whereYear('created_at', $m->year)
                ->sum('total');
            $monthly[] = ['label' => $m->format('M y'), 'revenue' => round($rev, 2)];
        }

        // ── Top 5 products ───────────────────────────────────
        $topProducts = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.vendor_id', $vid)
            ->selectRaw('products.name, SUM(sale_items.quantity) as total_sold, SUM(sale_items.subtotal) as revenue')
            ->groupBy('sale_items.product_id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // ── Payment status breakdown ─────────────────────────
        $paymentStatus = Sale::withoutGlobalScopes()
            ->where('vendor_id', $vid)
            ->selectRaw('payment_status as name, COUNT(*) as value')
            ->groupBy('payment_status')
            ->get()
            ->map(fn($r) => ['name' => ucfirst($r->name), 'value' => (int) $r->value]);

        // ── Recent 10 sales ──────────────────────────────────
        $recentSales = Sale::withoutGlobalScopes()
            ->with('customer')
            ->where('vendor_id', $vid)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'id'             => $s->id,
                'invoice_no'     => $s->invoice_no,
                'customer'       => $s->customer?->name ?? 'Walk-in',
                'total'          => $s->total,
                'paid_amount'    => $s->paid_amount,
                'due_amount'     => $s->due_amount,
                'payment_status' => $s->payment_status,
                'date'           => $s->created_at->format('d M Y'),
            ]);

        // ── Team ─────────────────────────────────────────────
        $team = User::where('vendor_id', $vid)->with('roles')->get()
            ->map(fn($u) => [
                'name'  => $u->name,
                'email' => $u->email,
                'role'  => $u->getRoleNames()->first() ?? '—',
            ]);

        return response()->json([
            'vendor'         => $vendor,
            'stats'          => $stats,
            'monthly_revenue'=> $monthly,
            'top_products'   => $topProducts,
            'payment_status' => $paymentStatus,
            'recent_sales'   => $recentSales,
            'team'           => $team,
        ]);
    }

    public function userIndex()
    {
        $users = User::with('vendor', 'roles')->latest()->get()
            ->map(fn($u) => array_merge($u->toArray(), [
                'roles'       => $u->getRoleNames(),
                'vendor_name' => $u->vendor?->name ?? 'Super Admin',
            ]));

        return response()->json($users);
    }
}
