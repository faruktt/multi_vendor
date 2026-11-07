<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\CourierSetting;
use App\Models\Customer;
use App\Models\IncomeExpense;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Reseller;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }
        if ($request->filled('status')) {
            $val = $request->status;
            if (in_array($val, ['paid', 'partial', 'pending', 'unpaid'])) {
                if (!$request->filled('payment_status')) {
                    $query->where('payment_status', $val === 'unpaid' ? 'pending' : $val);
                }
            } else {
                if (!$request->filled('order_status')) {
                    $query->where('order_status', $val);
                }
            }
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
            'revenue' => Sale::withoutGlobalScopes()->where('vendor_id', $v->id)->completedOrPos()->sum('total'),
            'due'     => Sale::withoutGlobalScopes()->where('vendor_id', $v->id)->completedOrPos()->sum('due_amount'),
        ]);

        // Channel breakdown counters
        $channelStats = [
            'total'    => Sale::withoutGlobalScopes()->count(),
            'website'  => Sale::withoutGlobalScopes()->where('channel', 'web')->whereNull('reseller_id')->whereNull('supplier_id')->whereDoesntHave('saleItems', fn($q) => $q->whereNotNull('supplier_id'))->count(),
            'pos'      => Sale::withoutGlobalScopes()->where('channel', 'pos')->count(),
            'reseller' => Sale::withoutGlobalScopes()->where(fn($q) => $q->whereNotNull('reseller_id')->orWhere('channel', 'reseller'))->count(),
            'supplier' => Sale::withoutGlobalScopes()->where(fn($q) => $q->whereNotNull('supplier_id')->orWhereHas('saleItems', fn($sq) => $sq->whereNotNull('supplier_id')))->count(),
        ];

        // Calculate repeat order count per phone number on the current page
        $phonesOnPage = $sales->map(function ($s) {
            $p = $s->customer?->phone ?? $s->reseller?->phone ?? $s->supplier?->phone ?? '';
            $clean = preg_replace('/[^\d]/', '', (string)$p);
            if (str_starts_with($clean, '880')) $clean = substr($clean, 2);
            if (strlen($clean) > 11) $clean = substr($clean, -11);
            return $clean ?: null;
        })->filter()->unique()->values()->toArray();

        $repeatOrderCounts = [];
        if (!empty($phonesOnPage)) {
            $matchingSales = Sale::withoutGlobalScopes()
                ->where(function($q) use ($phonesOnPage) {
                    $q->whereHas('customer', function($cq) use ($phonesOnPage) {
                        $cq->where(function($inner) use ($phonesOnPage) {
                            foreach ($phonesOnPage as $p) {
                                $inner->orWhere('phone', 'like', "%{$p}");
                            }
                        });
                    })->orWhereHas('reseller', function($rq) use ($phonesOnPage) {
                        $rq->where(function($inner) use ($phonesOnPage) {
                            foreach ($phonesOnPage as $p) {
                                $inner->orWhere('phone', 'like', "%{$p}");
                            }
                        });
                    })->orWhereHas('supplier', function($sq) use ($phonesOnPage) {
                        $sq->where(function($inner) use ($phonesOnPage) {
                            foreach ($phonesOnPage as $p) {
                                $inner->orWhere('phone', 'like', "%{$p}");
                            }
                        });
                    });
                })
                ->with(['customer:id,phone', 'reseller:id,phone', 'supplier:id,phone'])
                ->get();

            foreach ($matchingSales as $ms) {
                $p = $ms->customer?->phone ?? $ms->reseller?->phone ?? $ms->supplier?->phone ?? '';
                $clean = preg_replace('/[^\d]/', '', (string)$p);
                if (str_starts_with($clean, '880')) $clean = substr($clean, 2);
                if (strlen($clean) > 11) $clean = substr($clean, -11);
                if ($clean) {
                    $repeatOrderCounts[$clean] = ($repeatOrderCounts[$clean] ?? 0) + 1;
                }
            }
        }

        $paymentMethods = PaymentMethod::options();
        $activeCouriers = CourierSetting::active()->get();
        $orderStatuses  = OrderStatus::orderBy('sort_order')->get();

        return view('admin.all-sales', compact('sales', 'vendors', 'branchSummary', 'paymentMethods', 'activeCouriers', 'orderStatuses', 'channelStats', 'repeatOrderCounts'));
    }

    /**
     * Update order status from admin all-sales page
     */
    public function updateStatus(Request $request, Sale $sale)
    {
        $request->validate([
            'order_status' => ['required', Rule::in(OrderStatus::pluck('key'))],
        ]);

        $updateData = ['order_status' => $request->order_status];
        if (in_array(strtolower($request->order_status), ['delivered', 'completed', 'complete'])) {
            $updateData['payment_status'] = 'paid';
            $updateData['paid_amount']    = $sale->total;
            $updateData['due_amount']     = 0;
        }

        $sale->update($updateData);
        $freshSale = $sale->fresh();

        if ($request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'order_status'   => $freshSale->order_status,
                'payment_status' => $freshSale->payment_status,
                'paid_amount'    => (float) $freshSale->paid_amount,
                'due_amount'     => (float) $freshSale->due_amount,
                'message'        => 'Order status updated successfully.'
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
     * Batch BD Courier lookup for admin all-sales page (auto-loaded on page view)
     */
    public function bdCourierCheckBatch(Request $request)
    {
        $phones = $request->input('phones', []);
        if (!is_array($phones) || empty($phones)) {
            return response()->json(['success' => true, 'results' => []]);
        }

        $apiKey  = config('services.bd_courier.api_key') ?: env('BD_COURIER_API_KEY');
        $baseUrl = rtrim(config('services.bd_courier.base_url') ?: env('BD_COURIER_BASE_URL', 'https://api.bdcourier.com'), '/');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'BD_COURIER_API_KEY is not configured',
                'results' => []
            ]);
        }

        $results = [];
        $phonesToFetch = [];

        foreach ($phones as $phone) {
            $cleanPhone = preg_replace('/[^\d]/', '', (string)$phone);
            if (str_starts_with($cleanPhone, '880')) {
                $cleanPhone = substr($cleanPhone, 2);
            }
            if (strlen($cleanPhone) > 11) {
                $cleanPhone = substr($cleanPhone, -11);
            }

            if (strlen($cleanPhone) < 10) {
                continue;
            }

            $cacheKey = 'bd_courier_summary_' . $cleanPhone;
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                $results[$cleanPhone] = $cached;
            } else {
                $phonesToFetch[$cleanPhone] = $cleanPhone;
            }
        }

        if (!empty($phonesToFetch)) {
            $chunks = array_chunk($phonesToFetch, 25, true);
            foreach ($chunks as $chunk) {
                $responses = Http::pool(function ($pool) use ($chunk, $baseUrl, $apiKey) {
                    $reqs = [];
                    foreach ($chunk as $cleanPhone) {
                        $reqs[$cleanPhone] = $pool->as($cleanPhone)
                            ->withoutVerifying()
                            ->withHeaders([
                                'Authorization' => 'Bearer ' . $apiKey,
                                'Content-Type'  => 'application/json',
                                'Accept'        => 'application/json',
                            ])
                            ->timeout(6)
                            ->post($baseUrl . '/courier-check', [
                                'phone' => $cleanPhone,
                            ]);
                    }
                    return $reqs;
                });

                foreach ($chunk as $cleanPhone) {
                    $response = $responses[$cleanPhone] ?? null;
                    if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                        $json = $response->json();
                        $payload = $json['data'] ?? $json;
                        $summary = $payload['summary'] ?? null;

                        $total     = (int)($summary['total_parcel'] ?? $payload['total_parcel'] ?? $payload['total_parcels'] ?? 0);
                        $delivered = (int)($summary['success_parcel'] ?? $payload['success_parcel'] ?? $payload['total_delivered'] ?? 0);
                        $cancelled = (int)($summary['cancelled_parcel'] ?? $payload['cancelled_parcel'] ?? $payload['total_cancel'] ?? 0);

                        // If summary totals are 0, aggregate from individual couriers if present
                        if ($total === 0 && is_array($payload)) {
                            foreach ($payload as $k => $v) {
                                if ($k === 'summary' || !is_array($v)) continue;
                                $total     += (int)($v['total_parcel'] ?? 0);
                                $delivered += (int)($v['success_parcel'] ?? 0);
                                $cancelled += (int)($v['cancelled_parcel'] ?? 0);
                            }
                        }

                        // If steadfast has parcel_range and total is still 0
                        if ($total === 0 && !empty($payload['steadfast']['parcel_range'])) {
                            $range = (int)$payload['steadfast']['parcel_range'];
                            if ($range > 0) {
                                $total = $range;
                                $sRatio = (float)($summary['success_ratio'] ?? $payload['steadfast']['success_ratio'] ?? 0);
                                $delivered = (int)round(($sRatio / 100) * $total);
                                $cancelled = max(0, $total - $delivered);
                            }
                        }

                        $successRatio  = (float)($summary['success_ratio'] ?? ($total > 0 ? round(($delivered / $total) * 100, 1) : 0));
                        $deliveryRatio = $total > 0 ? (int)round(($delivered / $total) * 100) : (int)round($successRatio);
                        $cancelRatio   = $total > 0 ? (int)round(($cancelled / $total) * 100) : ($deliveryRatio > 0 ? max(0, 100 - $deliveryRatio) : 0);

                        $level = 'none';
                        $color = 'slate';
                        $label = '0% New';

                        if ($total > 0 || $successRatio > 0) {
                            if ($deliveryRatio >= 80) {
                                $level = 'safe';
                                $color = 'emerald';
                                $label = $deliveryRatio . '% Safe';
                            } elseif ($deliveryRatio >= 60) {
                                $level = 'good';
                                $color = 'sky';
                                $label = $deliveryRatio . '% Good';
                            } elseif ($deliveryRatio >= 40) {
                                $level = 'average';
                                $color = 'amber';
                                $label = $deliveryRatio . '% Medium';
                            } else {
                                $level = 'risky';
                                $color = 'red';
                                $label = $deliveryRatio . '% Risky';
                            }
                        }

                        $info = [
                            'phone'            => $cleanPhone,
                            'total_parcel'     => $total,
                            'success_parcel'   => $delivered,
                            'cancelled_parcel' => $cancelled,
                            'success_ratio'    => $successRatio,
                            'delivery_ratio'   => $deliveryRatio,
                            'cancel_ratio'     => $cancelRatio,
                            'level'            => $level,
                            'color'            => $color,
                            'label'            => $label,
                        ];

                        Cache::put('bd_courier_summary_' . $cleanPhone, $info, now()->addHours(6));
                        $results[$cleanPhone] = $info;
                    } else {
                        $fallback = [
                            'phone'            => $cleanPhone,
                            'total_parcel'     => 0,
                            'success_parcel'   => 0,
                            'cancelled_parcel' => 0,
                            'success_ratio'    => 0,
                            'delivery_ratio'   => 0,
                            'cancel_ratio'     => 0,
                            'level'            => 'none',
                            'color'            => 'slate',
                            'label'            => '0% New',
                        ];
                        Cache::put('bd_courier_summary_' . $cleanPhone, $fallback, now()->addMinutes(30));
                        $results[$cleanPhone] = $fallback;
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
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
            ->with(['vendor', 'category', 'subCategory'])
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
        $query = Category::withCount(['products', 'subCategories']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where('name', 'like', "%{$s}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->get();
        $parentCategories = $categories;

        return view('admin.all-categories', compact('categories', 'parentCategories'));
    }

    public function allSubcategories(Request $request)
    {
        $query = SubCategory::with('category')->withCount('products');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhereHas('category', fn($cq) => $cq->where('name', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('parent_id') || $request->filled('category_id') || $request->filled('category')) {
            $catFilter = trim($request->category_id ?: ($request->parent_id ?: $request->category));
            $query->where(function ($q) use ($catFilter) {
                if (is_numeric($catFilter)) {
                    $q->where('category_id', $catFilter);
                } else {
                    $q->whereHas('category', fn($cq) => $cq->where('name', $catFilter));
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subcategories = $query->orderBy('sort_order')->orderBy('name')->get();
        $parentCategories = Category::orderBy('name')->get();

        return view('admin.all-subcategories', compact('subcategories', 'parentCategories'));
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

    // ── All Suppliers (Purchase Suppliers) ────────────────────────────────
    public function allSuppliers(Request $request)
    {
        $query = Supplier::withoutGlobalScopes()
            ->purchase()
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
            'count'   => Supplier::withoutGlobalScopes()->purchase()->where('vendor_id', $v->id)->count(),
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
            'revenue'  => Sale::withoutGlobalScopes()->where('vendor_id', $v->id)->completedOrPos()->sum('total'),
        ]);

        // Main paginated list
        $query = Customer::withoutGlobalScopes()
            ->with('vendor')
            ->withCount(['sales' => fn($q) => $q->withoutGlobalScopes()])
            ->withSum(['sales as total_spent' => fn($q) => $q->withoutGlobalScopes()->completedOrPos()], 'total')
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

    // ── Sales & Order Report ──────────────────────────────────────────────
    public function salesReport(Request $request)
    {
        $vendors       = Vendor::where('is_warehouse', false)->orderBy('name')->get();
        $suppliers     = Supplier::marketplace()->orderBy('name')->get();
        $resellers     = Reseller::orderBy('name')->get();
        $orderStatuses = OrderStatus::orderBy('sort_order')->get();

        // Base query for report filtering
        $baseQuery = Sale::withoutGlobalScopes()
            ->with([
                'vendor',
                'customer',
                'reseller',
                'supplier',
                'saleItems.supplier',
                'saleItems.product',
                'saleItems.variant'
            ]);

        // Branch filter
        if ($request->filled('vendor_id')) {
            $baseQuery->where('vendor_id', $request->vendor_id);
        }
        // Payment status filter
        if ($request->filled('status')) {
            $baseQuery->where('payment_status', $request->status);
        }
        // Order status filter
        if ($request->filled('order_status')) {
            $baseQuery->where('order_status', $request->order_status);
        }
        // Supplier filter
        if ($request->filled('supplier_id')) {
            $sId = (int) $request->supplier_id;
            $baseQuery->where(fn($q) => $q->where('supplier_id', $sId)->orWhereHas('saleItems', fn($sq) => $sq->where('supplier_id', $sId)));
        }
        // Reseller filter
        if ($request->filled('reseller_id')) {
            $baseQuery->where('reseller_id', $request->reseller_id);
        }
        // Date range filter
        if ($request->filled('from')) {
            $baseQuery->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $baseQuery->whereDate('created_at', '<=', $request->to);
        }
        // Search filter (invoice, tracking, customer)
        if ($request->filled('search')) {
            $s = trim($request->search);
            $baseQuery->where(function ($q) use ($s) {
                $q->where('invoice_no', 'like', "%{$s}%")
                  ->orWhere('courier_tracking_code', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        // Fetch all matching sales in this filter set to calculate complete statistics
        $filteredAllSales = (clone $baseQuery)->get();

        // Categorize into: Admin in-house, Supplier products, and Reseller orders
        $adminSales = $filteredAllSales->filter(function ($s) {
            $isSupplier = !empty($s->supplier_id) || $s->saleItems->contains(fn($i) => !empty($i->supplier_id));
            $isReseller = $s->channel === 'reseller' || !empty($s->reseller_id);
            return !$isSupplier && !$isReseller;
        });

        $supplierSales = $filteredAllSales->filter(function ($s) {
            $isSupplier = !empty($s->supplier_id) || $s->saleItems->contains(fn($i) => !empty($i->supplier_id));
            $isReseller = $s->channel === 'reseller' || !empty($s->reseller_id);
            return $isSupplier && !$isReseller;
        });

        $resellerSales = $filteredAllSales->filter(function ($s) {
            return $s->channel === 'reseller' || !empty($s->reseller_id);
        });

        // Compute complete calculation metrics for each group
        $globalStats   = $this->calculateSalesMetrics($filteredAllSales);
        $adminStats    = $this->calculateSalesMetrics($adminSales);
        $supplierStats = $this->calculateSalesMetrics($supplierSales);
        $resellerStats = $this->calculateSalesMetrics($resellerSales);

        // List of returned orders within the filtered scope (for detailed return audit)
        $returnedOrders = $filteredAllSales->filter(fn($s) => in_array(strtolower((string)$s->order_status), ['return', 'returned']));

        // Per-branch summary respecting active filters
        $branchSummary = $vendors->map(function ($v) use ($filteredAllSales) {
            $vSales = $filteredAllSales->where('vendor_id', $v->id);
            $vEligible = $vSales->filter(fn($s) => $s->isEligibleForRevenue());
            $gross = (float) $vEligible->sum('total');
            $vReturns = $vSales->filter(fn($s) => in_array(strtolower((string)$s->order_status), ['return', 'returned']));
            $vRetAmt = (float) $vReturns->sum('total');
            $vRetShip = (float) $vReturns->sum('delivery_charge');
            return [
                'vendor'                 => $v,
                'orders'                 => $vSales->count(),
                'revenue'                => $gross,
                'return_orders'          => $vReturns->count(),
                'return_shipping_charge' => $vRetShip,
                'net_revenue'            => (float) ($gross - $vRetAmt - $vRetShip),
                'due'                    => (float) $vEligible->sum('due_amount'),
            ];
        });

        // Paginated orders list with optional source filter
        $tableQuery = clone $baseQuery;
        if ($request->filled('source')) {
            $src = strtolower(trim($request->source));
            if ($src === 'admin') {
                $tableQuery->whereNull('reseller_id')
                    ->where('channel', '!=', 'reseller')
                    ->whereNull('supplier_id')
                    ->whereDoesntHave('saleItems', fn($sq) => $sq->whereNotNull('supplier_id'));
            } elseif ($src === 'supplier') {
                $tableQuery->where(function ($q) {
                    $q->whereNotNull('supplier_id')
                      ->orWhereHas('saleItems', fn($sq) => $sq->whereNotNull('supplier_id'));
                })->where(function ($q) {
                    $q->whereNull('reseller_id')->where('channel', '!=', 'reseller');
                });
            } elseif ($src === 'reseller') {
                $tableQuery->where(fn($q) => $q->whereNotNull('reseller_id')->orWhere('channel', 'reseller'));
            } elseif ($src === 'return' || $src === 'returned') {
                $tableQuery->whereIn('order_status', ['return', 'returned']);
            }
        }

        $sales = $tableQuery->latest()->paginate(25)->withQueryString();

        return view('admin.sales-report', compact(
            'sales',
            'vendors',
            'suppliers',
            'resellers',
            'orderStatuses',
            'branchSummary',
            'globalStats',
            'adminStats',
            'supplierStats',
            'resellerStats',
            'returnedOrders'
        ));
    }

    /**
     * Helper to compute detailed sales calculations, returns, and shipping charge deductions
     */
    private function calculateSalesMetrics($sales): array
    {
        $totalOrders   = $sales->count();
        $eligibleSales = $sales->filter(fn($s) => $s->isEligibleForRevenue());
        $grossSales    = (float) $eligibleSales->sum('total');
        $paidAmount    = (float) $eligibleSales->sum('paid_amount');
        $dueAmount     = (float) $eligibleSales->sum('due_amount');
        $totalShipping = (float) $sales->sum('delivery_charge');

        $delivered = $sales->filter(fn($s) => in_array(strtolower((string)$s->order_status), ['delivered', 'completed', 'complete']));
        $deliveredCount = $delivered->count();
        $deliveredAmount = (float) $delivered->sum('total');

        $pending = $sales->filter(fn($s) => in_array(strtolower((string)$s->order_status), ['pending', 'processing', 'sent_to_courier', 'out_for_delivery']));
        $pendingCount = $pending->count();
        $pendingAmount = (float) $pending->sum('total');

        $cancelled = $sales->filter(fn($s) => strtolower((string)$s->order_status) === 'cancelled');
        $cancelledCount = $cancelled->count();
        $cancelledAmount = (float) $cancelled->sum('total');

        $returned = $sales->filter(fn($s) => in_array(strtolower((string)$s->order_status), ['return', 'returned']));
        $returnCount = $returned->count();
        $returnAmount = (float) $returned->sum('total');
        // Return shipping charge (shipping cost lost on return — to be subtracted)
        $returnShippingCharge = (float) $returned->sum('delivery_charge');

        // Net calculation: Gross Sales minus Return Amount minus Return Shipping Charge Loss
        $netRevenue = (float) ($grossSales - $returnAmount - $returnShippingCharge);

        // Realized net calculation: Delivered Amount minus Return Shipping Charge Loss
        $deliveredNet = (float) ($deliveredAmount - $returnShippingCharge);

        return [
            'total_orders'           => $totalOrders,
            'gross_sales'            => $grossSales,
            'paid_amount'            => $paidAmount,
            'due_amount'             => $dueAmount,
            'total_shipping'         => $totalShipping,
            'delivered_count'        => $deliveredCount,
            'delivered_amount'       => $deliveredAmount,
            'pending_count'          => $pendingCount,
            'pending_amount'         => $pendingAmount,
            'cancelled_count'        => $cancelledCount,
            'cancelled_amount'       => $cancelledAmount,
            'return_count'           => $returnCount,
            'return_amount'          => $returnAmount,
            'return_shipping_charge' => $returnShippingCharge,
            'net_revenue'            => $netRevenue,
            'delivered_net'          => $deliveredNet,
            'return_rate'            => $totalOrders > 0 ? round(($returnCount / $totalOrders) * 100, 1) : 0,
            'delivery_rate'          => $totalOrders > 0 ? round(($deliveredCount / $totalOrders) * 100, 1) : 0,
        ];
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
