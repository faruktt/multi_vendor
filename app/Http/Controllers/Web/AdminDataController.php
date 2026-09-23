<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CourierSetting;
use App\Models\Customer;
use App\Models\IncomeExpense;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class AdminDataController extends Controller
{
    // ── All Sales (All Channels: Website, POS, Reseller, Supplier) ────────
    public function allSales(Request $request)
    {
        $query = Sale::withoutGlobalScopes()
            ->with([
                'vendor',
                'customer',
                'reseller',
                'supplier',
                'notes.user.roles',
                'saleItems.product',
                'saleItems.variant',
                'saleItems.supplier'
            ])
            ->withCount('notes')
            ->latest();

        // Source / Channel filter
        if ($request->filled('source')) {
            $src = strtolower(trim($request->source));
            if ($src === 'website' || $src === 'web') {
                $query->where('channel', 'web')
                    ->whereNull('reseller_id')
                    ->whereNull('supplier_id')
                    ->whereDoesntHave('saleItems', fn($q) => $q->whereNotNull('supplier_id'));
            } elseif ($src === 'pos') {
                $query->where('channel', 'pos');
            } elseif ($src === 'reseller') {
                $query->where(fn($q) => $q->whereNotNull('reseller_id')->orWhere('channel', 'reseller'));
            } elseif ($src === 'supplier') {
                $query->where(fn($q) => $q->whereNotNull('supplier_id')->orWhereHas('saleItems', fn($sq) => $sq->whereNotNull('supplier_id')));
            }
        } elseif ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }
        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('invoice_no', 'like', "%{$s}%")
                  ->orWhere('courier_tracking_code', 'like', "%{$s}%")
                  ->orWhere('courier_consignment_id', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($cq) use ($s) {
                      $cq->where('name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  })
                  ->orWhereHas('reseller', function ($rq) use ($s) {
                      $rq->where('name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  })
                  ->orWhereHas('supplier', function ($sq) use ($s) {
                      $sq->where('name', 'like', "%{$s}%")
                         ->orWhere('company_name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  })
                  ->orWhereHas('saleItems.supplier', function ($sq) use ($s) {
                      $sq->where('name', 'like', "%{$s}%")
                         ->orWhere('company_name', 'like', "%{$s}%");
                  });
            });
        }

        $sales   = $query->paginate(20)->withQueryString();
        // Warehouse never sells — irrelevant here.
        $vendors = Vendor::where('is_warehouse', false)->orderBy('name')->get();
        $branchSummary = $vendors->map(fn($v) => [
            'vendor'  => $v,
            'orders'  => Sale::withoutGlobalScopes()->where('vendor_id', $v->id)->count(),
            'revenue' => Sale::withoutGlobalScopes()->where('vendor_id', $v->id)->sum('total'),
            'due'     => Sale::withoutGlobalScopes()->where('vendor_id', $v->id)->sum('due_amount'),
        ]);

        // Channel breakdown counters
        $channelStats = [
            'total'    => Sale::withoutGlobalScopes()->count(),
            'website'  => Sale::withoutGlobalScopes()->where('channel', 'web')->whereNull('reseller_id')->whereNull('supplier_id')->whereDoesntHave('saleItems', fn($q) => $q->whereNotNull('supplier_id'))->count(),
            'pos'      => Sale::withoutGlobalScopes()->where('channel', 'pos')->count(),
            'reseller' => Sale::withoutGlobalScopes()->where(fn($q) => $q->whereNotNull('reseller_id')->orWhere('channel', 'reseller'))->count(),
            'supplier' => Sale::withoutGlobalScopes()->where(fn($q) => $q->whereNotNull('supplier_id')->orWhereHas('saleItems', fn($sq) => $sq->whereNotNull('supplier_id')))->count(),
        ];

        $paymentMethods = PaymentMethod::options();
        $activeCouriers = CourierSetting::active()->get();
        $orderStatuses  = OrderStatus::orderBy('sort_order')->get();

        return view('admin.all-sales', compact('sales', 'vendors', 'branchSummary', 'paymentMethods', 'activeCouriers', 'orderStatuses', 'channelStats'));
    }

    /**
     * Update order status from admin all-sales page
     */
    public function updateStatus(Request $request, Sale $sale)
    {
        $request->validate([
            'order_status' => ['required', Rule::in(OrderStatus::pluck('key'))],
        ]);

        $sale->update(['order_status' => $request->order_status]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'order_status' => $sale->order_status,
                'message'      => 'Order status updated successfully.'
            ]);
        }

        return back()->with('success', 'Order status updated.');
    }

    /**
     * BD Courier lookup for admin all-sales page
     */
    public function bdCourierCheck(Request $request)
    {
        $phone = trim($request->input('phone', ''));
        if ($phone === '') {
            return response()->json(['error' => 'ফোন নম্বর পাওয়া যায়নি।'], 422);
        }

        $cleanPhone = preg_replace('/[^\d]/', '', $phone);
        if (str_starts_with($cleanPhone, '880')) {
            $cleanPhone = substr($cleanPhone, 2);
        }
        if (strlen($cleanPhone) > 11) {
            $cleanPhone = substr($cleanPhone, -11);
        }

        $apiKey  = config('services.bd_courier.api_key') ?: env('BD_COURIER_API_KEY');
        $baseUrl = rtrim(config('services.bd_courier.base_url') ?: env('BD_COURIER_BASE_URL', 'https://api.bdcourier.com'), '/');

        if (empty($apiKey)) {
            return response()->json([
                'error' => '.env ফাইলে BD_COURIER_API_KEY দেওয়া নেই। দয়া করে আপনার BD Courier API Key টি .env ফাইলে যুক্ত করুন।',
            ], 422);
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])
                ->timeout(20)
                ->post($baseUrl . '/courier-check', [
                    'phone' => $cleanPhone,
                ]);

            if ($response->successful()) {
                $json = $response->json();
                $payload = $json['data'] ?? $json;
                $summary = $payload['summary'] ?? null;

                if (!$summary) {
                    $total     = (int)($payload['total_parcel'] ?? $payload['total_parcels'] ?? 0);
                    $delivered = (int)($payload['success_parcel'] ?? $payload['total_delivered'] ?? 0);
                    $cancelled = (int)($payload['cancelled_parcel'] ?? $payload['total_cancel'] ?? ($total - $delivered));
                    $ratio     = $total > 0 ? round(($delivered / $total) * 100, 1) : 0;
                    $summary   = [
                        'total_parcel'     => $total,
                        'success_parcel'   => $delivered,
                        'cancelled_parcel' => $cancelled,
                        'success_ratio'    => $ratio,
                    ];
                }

                $couriers = [];
                if (is_array($payload)) {
                    foreach ($payload as $key => $val) {
                        if ($key === 'summary' || !is_array($val)) continue;
                        $couriers[] = [
                            'key'              => $key,
                            'name'             => $val['name'] ?? ucfirst($key),
                            'logo'             => $val['logo'] ?? null,
                            'total_parcel'     => (int)($val['total_parcel'] ?? 0),
                            'success_parcel'   => (int)($val['success_parcel'] ?? 0),
                            'cancelled_parcel' => (int)($val['cancelled_parcel'] ?? 0),
                            'success_ratio'    => (float)($val['success_ratio'] ?? 0),
                        ];
                    }
                }

                return response()->json([
                    'success'  => true,
                    'phone'    => $cleanPhone,
                    'summary'  => $summary,
                    'couriers' => $couriers,
                    'reports'  => $json['reports'] ?? [],
                    'raw'      => $json,
                ]);
            }

            $err = $response->json('message') ?? $response->json('error') ?? ('BD Courier API error (Status ' . $response->status() . ')');
            return response()->json(['error' => $err], 400);

        } catch (\Exception $e) {
            return response()->json(['error' => 'BD Courier সার্ভারে সংযোগ করা সম্ভব হয়নি: ' . $e->getMessage()], 502);
        }
    }

    /**
     * Get notes list for admin all-sales
     */
    public function getNotes(Sale $sale)
    {
        $notes = $sale->notes()->with('user.roles')->latest()->get()->map(function ($n) {
            $user = $n->user;
            $role = $user ? ($user->roles->first()?->name ?? 'Staff') : 'Staff';
            return [
                'id'                   => $n->id,
                'note'                 => $n->note,
                'user_name'            => $user ? $user->name : 'User #' . $n->user_id,
                'user_role'            => ucfirst($role),
                'created_at_formatted' => $n->created_at->format('d M Y, h:i A'),
                'time_ago'             => $n->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'notes'   => $notes,
            'count'   => $notes->count(),
        ]);
    }

    /**
     * Store a new note for admin all-sales
     */
    public function addNote(Request $request, Sale $sale)
    {
        $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $note = $sale->notes()->create([
            'user_id' => auth()->id(),
            'note'    => trim($request->input('note')),
        ]);

        $note->load('user.roles');
        $user = $note->user;
        $role = $user ? ($user->roles->first()?->name ?? 'Staff') : 'Staff';

        return response()->json([
            'success' => true,
            'message' => 'নোট সফলভাবে সংরক্ষণ করা হয়েছে।',
            'note'    => [
                'id'                   => $note->id,
                'note'                 => $note->note,
                'user_name'            => $user ? $user->name : 'User #' . $note->user_id,
                'user_role'            => ucfirst($role),
                'created_at_formatted' => $note->created_at->format('d M Y, h:i A'),
                'time_ago'             => $note->created_at->diffForHumans(),
            ],
            'count'   => $sale->notes()->count(),
        ]);
    }

    // ── All Products ───────────────────────────────────────────────────────
    public function allProducts(Request $request)
    {
        // Warehouse's own rows are internal clones of branch products, not distinct catalog items.
        $query = Product::withoutGlobalScopes()
            ->whereHas('vendor', fn($q) => $q->where('is_warehouse', false))
            ->with(['vendor', 'category'])
            ->latest();

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('sku', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('low_stock')) {
            $query->where('stock_qty', '<=', 10);
        }

        $products = $query->paginate(20)->withQueryString();
        $vendors  = Vendor::where('is_warehouse', false)->orderBy('name')->get();
        $branchSummary = $vendors->map(fn($v) => [
            'vendor'    => $v,
            'count'     => Product::withoutGlobalScopes()->where('vendor_id', $v->id)->count(),
            'low_stock' => Product::withoutGlobalScopes()->where('vendor_id', $v->id)->where('stock_qty', '>', 0)->where('stock_qty', '<=', 10)->count(),
            'out_stock' => Product::withoutGlobalScopes()->where('vendor_id', $v->id)->where('stock_qty', 0)->count(),
        ]);

        return view('admin.all-products', compact('products', 'vendors', 'branchSummary'));
    }

    // ── All Categories ─────────────────────────────────────────────────────
    public function allCategories(Request $request)
    {
        // Warehouse's only category is its own hidden auto-created "General" bucket — internal noise here.
        $query = Category::withoutGlobalScopes()
            ->whereHas('vendor', fn($q) => $q->where('is_warehouse', false))
            ->with('vendor')
            ->withCount(['products' => fn($q) => $q->withoutGlobalScopes()])
            ->latest();

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $categories = $query->paginate(25)->withQueryString();
        $vendors    = Vendor::where('is_warehouse', false)->orderBy('name')->get();
        $branchSummary = $vendors->map(fn($v) => [
            'vendor'   => $v,
            'count'    => Category::withoutGlobalScopes()->where('vendor_id', $v->id)->count(),
            'products' => Product::withoutGlobalScopes()->where('vendor_id', $v->id)->count(),
        ]);

        return view('admin.all-categories', compact('categories', 'vendors', 'branchSummary'));
    }

    // ── All Purchases ──────────────────────────────────────────────────────
    public function allPurchases(Request $request)
    {
        $query = Purchase::withoutGlobalScopes()
            ->with(['vendor', 'supplier'])
            ->latest();

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $query->where('invoice_no', 'like', '%'.$request->search.'%');
        }

        $purchases = $query->paginate(20)->withQueryString();
        $vendors   = Vendor::orderBy('name')->get();
        $branchSummary = $vendors->map(fn($v) => [
            'vendor'  => $v,
            'count'   => Purchase::withoutGlobalScopes()->where('vendor_id', $v->id)->count(),
            'total'   => Purchase::withoutGlobalScopes()->where('vendor_id', $v->id)->sum('total'),
            'due'     => Purchase::withoutGlobalScopes()->where('vendor_id', $v->id)->sum('due_amount'),
        ]);

        $paymentMethods = PaymentMethod::options();

        return view('admin.all-purchases', compact('purchases', 'vendors', 'branchSummary', 'paymentMethods'));
    }

    // ── All Suppliers ──────────────────────────────────────────────────────
    public function allSuppliers(Request $request)
    {
        $query = Supplier::withoutGlobalScopes()
            ->with('vendor')
            ->withCount(['purchases' => fn($q) => $q->withoutGlobalScopes()])
            ->withSum(['purchases as total_purchase' => fn($q) => $q->withoutGlobalScopes()], 'total')
            ->withSum(['purchases as total_due' => fn($q) => $q->withoutGlobalScopes()], 'due_amount')
            ->latest();

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%');
            });
        }

        $suppliers = $query->paginate(20)->withQueryString();
        $vendors   = Vendor::orderBy('name')->get();
        $branchSummary = $vendors->map(fn($v) => [
            'vendor'  => $v,
            'count'   => Supplier::withoutGlobalScopes()->where('vendor_id', $v->id)->count(),
            'total'   => Purchase::withoutGlobalScopes()->where('vendor_id', $v->id)->sum('total'),
        ]);

        return view('admin.all-suppliers', compact('suppliers', 'vendors', 'branchSummary'));
    }

    // ── Stock Report ───────────────────────────────────────────────────────
    public function stockReport(Request $request)
    {
        $tab     = $request->input('tab', 'stock');
        $vendors = Vendor::orderBy('name')->get();

        $branchSummary = $vendors->map(fn($v) => [
            'vendor'    => $v,
            'count'     => Product::withoutGlobalScopes()->where('vendor_id', $v->id)->count(),
            'out_stock' => Product::withoutGlobalScopes()->where('vendor_id', $v->id)->where('stock_qty', 0)->count(),
            'value'     => Product::withoutGlobalScopes()->where('vendor_id', $v->id)->selectRaw('SUM(stock_qty * cost_price) as val')->value('val') ?? 0,
        ]);

        // Stock list (tab=stock)
        $query = Product::withoutGlobalScopes()
            ->with(['vendor', 'category'])
            ->orderBy('stock_qty');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('filter')) {
            match ($request->filter) {
                'low'  => $query->where('stock_qty', '>', 0)->where('stock_qty', '<=', 10),
                'out'  => $query->where('stock_qty', 0),
                'ok'   => $query->where('stock_qty', '>', 10),
                default => null,
            };
        }
        $products = $query->paginate(25, ['*'], 'ppage')->withQueryString();
        Product::attachStockMetrics($products, $request->filled('vendor_id') ? (int) $request->vendor_id : null);

        // Stock movements (tab=movements)
        $movQuery = StockMovement::withoutGlobalScopes()
            ->with(['product', 'vendor'])
            ->latest();

        if ($request->filled('vendor_id')) {
            $movQuery->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('type')) {
            $movQuery->where('type', $request->type);
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
        if ($request->filled('prod')) {
            $movQuery->whereHas('product', fn($q) => $q->where('name', 'like', '%'.$request->prod.'%'));
        }
        $movements = $movQuery->paginate(25, ['*'], 'mpage')->withQueryString();

        return view('admin.stock-report', compact('products', 'movements', 'vendors', 'branchSummary', 'tab'));
    }

    // ── Fraud Check (API-based) ────────────────────────────────────────────
    public function fraudCheck(Request $request)
    {
        $phone     = trim($request->input('phone', ''));
        $apiResult = null;
        $apiError  = null;
        $customers = collect();

        if ($phone !== '') {
            // Find matching customers across all branches
            $customers = Customer::withoutGlobalScopes()
                ->with(['vendor'])
                ->withCount(['sales' => fn($q) => $q->withoutGlobalScopes()])
                ->withSum(['sales as total_spent' => fn($q) => $q->withoutGlobalScopes()], 'total')
                ->where('phone', $phone)
                ->get();

            // Call fraudchecker.link API
            try {
                $apiKey = env('FRAUD_CHECKER_API_KEY', '26dfafdb4ba3692d650917d71b420fbd');

                $response = Http::withoutVerifying()
                    ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->asForm()
                    ->post('https://fraudchecker.link/api/v1/qc/', ['phone' => $phone]);

                if ($response->successful()) {
                    $apiResult = $response->json();
                } else {
                    $apiError = 'API returned status ' . $response->status() . ': ' . $response->body();
                }
            } catch (\Exception $e) {
                $apiError = 'Connection failed: ' . $e->getMessage();
            }
        }

        return view('admin.fraud-check', compact('phone', 'apiResult', 'apiError', 'customers'));
    }

    // ── All Customers ─────────────────────────────────────────────────────
    public function allCustomers(Request $request)
    {
        // Warehouse never has customers — irrelevant here.
        $vendors = Vendor::where('is_warehouse', false)->orderBy('name')->get();

        // Per-branch summary for the top bar
        $branchSummary = $vendors->map(fn($v) => [
            'vendor'   => $v,
            'count'    => Customer::withoutGlobalScopes()->where('vendor_id', $v->id)->count(),
            'revenue'  => Sale::withoutGlobalScopes()->where('vendor_id', $v->id)->sum('total'),
        ]);

        // Main paginated list
        $query = Customer::withoutGlobalScopes()
            ->with('vendor')
            ->withCount(['sales' => fn($q) => $q->withoutGlobalScopes()])
            ->withSum(['sales as total_spent' => fn($q) => $q->withoutGlobalScopes()], 'total')
            ->latest();

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%');
            });
        }

        $customers = $query->paginate(25)->withQueryString();

        return view('admin.all-customers', compact('customers', 'vendors', 'branchSummary'));
    }

    // ── Sales Report ──────────────────────────────────────────────────────
    public function salesReport(Request $request)
    {
        // Warehouse never sells — irrelevant here.
        $vendors = Vendor::where('is_warehouse', false)->orderBy('name')->get();

        // Per-branch summary
        $branchSummary = $vendors->map(fn($v) => [
            'vendor'  => $v,
            'orders'  => Sale::withoutGlobalScopes()->where('vendor_id', $v->id)->count(),
            'revenue' => Sale::withoutGlobalScopes()->where('vendor_id', $v->id)->sum('total'),
            'due'     => Sale::withoutGlobalScopes()->where('vendor_id', $v->id)->sum('due_amount'),
        ]);

        // Main paginated list (same as allSales but with extra filters)
        $query = Sale::withoutGlobalScopes()
            ->with(['vendor', 'customer'])
            ->latest();

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $query->where('invoice_no', 'like', '%'.$request->search.'%');
        }

        $sales = $query->paginate(25)->withQueryString();

        return view('admin.sales-report', compact('sales', 'vendors', 'branchSummary'));
    }

    // ── Financial Report ──────────────────────────────────────────────────
    public function financialReport(Request $request)
    {
        // Warehouse has no Finance module access — irrelevant here.
        $vendors = Vendor::where('is_warehouse', false)->orderBy('name')->get();

        // Per-branch summary
        $branchSummary = $vendors->map(function ($v) {
            $income  = IncomeExpense::withoutGlobalScopes()->where('vendor_id', $v->id)->where('type', 'income')->sum('amount');
            $expense = IncomeExpense::withoutGlobalScopes()->where('vendor_id', $v->id)->where('type', 'expense')->sum('amount');
            return [
                'vendor'  => $v,
                'income'  => (float) $income,
                'expense' => (float) $expense,
                'profit'  => (float) ($income - $expense),
            ];
        });

        // Main paginated list
        $query = IncomeExpense::withoutGlobalScopes()
            ->with(['vendor', 'category'])
            ->latest('date');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('note', 'like', '%'.$request->search.'%')
                  ->orWhere('reference', 'like', '%'.$request->search.'%');
            });
        }

        $records = $query->paginate(25)->withQueryString();

        return view('admin.financial-report', compact('records', 'vendors', 'branchSummary'));
    }
}
