<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CourierSetting;
use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    /**
     * Same fraudchecker.link lookup used by the standalone Fraud Check pages,
     * returned as JSON for the in-page modal on the sales list (web orders only).
     */
    public function fraudCheck(Request $request, Vendor $branch)
    {
        $phone = trim($request->input('phone', ''));
        if ($phone === '') {
            return response()->json(['error' => 'Phone number is required.'], 422);
        }

        try {
            $apiKey = env('FRAUD_CHECKER_API_KEY', '26dfafdb4ba3692d650917d71b420fbd');

            $response = Http::withoutVerifying()
                ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->asForm()
                ->post('https://fraudchecker.link/api/v1/qc/', ['phone' => $phone]);

            if ($response->successful()) {
                return response()->json(['result' => $response->json()]);
            }

            return response()->json(['error' => 'API returned status ' . $response->status()], 502);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Connection failed: ' . $e->getMessage()], 502);
        }
    }

    /**
     * Look up customer courier delivery history from BD Courier API (https://api.bdcourier.com/courier-check)
     */
    public function bdCourierCheck(Request $request, Vendor $branch)
    {
        $phone = trim($request->input('phone', ''));
        if ($phone === '') {
            return response()->json(['error' => 'ফোন নম্বর পাওয়া যায়নি।'], 422);
        }

        // Clean phone number (strip whitespace, hyphens, country code 88)
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

                // Extract summary and per-courier details
                $payload = $json['data'] ?? $json;
                $summary = $payload['summary'] ?? null;

                // If summary wasn't in $payload['summary'], calculate or check root
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

                // Collect per-courier breakdown
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

    public function index(Request $request, Vendor $branch)
    {
        $query = Sale::withoutGlobalScopes()
            ->with(['customer', 'createdBy', 'notes.user.roles', 'saleItems.product', 'saleItems.variant'])
            ->withCount('notes')
            ->where('vendor_id', $branch->id)
            ->whereNull('supplier_id')
            ->whereDoesntHave('saleItems', fn($q) => $q->whereNotNull('supplier_id'))
            ->latest();

        if ($request->search) $query->where('invoice_no', 'like', '%' . $request->search . '%');
        if ($request->status) $query->where('payment_status', $request->status);
        if ($request->order_status) $query->where('order_status', $request->order_status);
        if ($request->channel) $query->where('channel', $request->channel);
        if ($request->from)   $query->whereDate('created_at', '>=', $request->from);
        if ($request->to)     $query->whereDate('created_at', '<=', $request->to);

        $sales = $query->paginate(20)->withQueryString();
        $orderStatuses = OrderStatus::orderBy('sort_order')->get();
        $paymentMethods = PaymentMethod::options();
        $activeCouriers = CourierSetting::active()->get();

        return view('sales.index', compact('branch', 'sales', 'orderStatuses', 'paymentMethods', 'activeCouriers'));
    }

    public function show(Vendor $branch, Sale $sale)
    {
        $sale->load([
            'saleItems.product', 'saleItems.variant.color', 'saleItems.returnItems',
            'customer', 'createdBy', 'payments',
            'returns.items.product', 'returns.createdBy',
            'notes.user.roles',
        ]);
        $orderStatuses = OrderStatus::orderBy('sort_order')->get();
        $paymentMethods = PaymentMethod::options();

        return view('sales.show', compact('branch', 'sale', 'orderStatuses', 'paymentMethods'));
    }

    /**
     * Get notes list for a sale
     */
    public function getNotes(Request $request, Vendor $branch, Sale $sale)
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
     * Store a new note for a sale
     */
    public function addNote(Request $request, Vendor $branch, Sale $sale)
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

    public function edit(Vendor $branch, Sale $sale)
    {
        $sale->load(['saleItems.product', 'saleItems.variant', 'customer']);

        $customers = Customer::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        $initialCart = $sale->saleItems->map(fn($item) => [
            '_key'         => $item->product_id . '_' . ($item->product_variant_id ?? 'base'),
            'product_id'   => $item->product_id,
            'name'         => $item->product?->name ?? 'Unknown Product',
            'quantity'     => (int) $item->quantity,
            'unit_price'   => (float) $item->unit_price,
            'variant_id'   => $item->product_variant_id,
            'variant_name' => $item->variant_name,
        ])->values();

        $paymentMethods = PaymentMethod::options();

        return view('sales.edit', compact('branch', 'sale', 'customers', 'initialCart', 'paymentMethods'));
    }

    public function update(Request $request, Vendor $branch, Sale $sale)
    {
        $request->validate([
            'customer_id'            => 'nullable|exists:customers,id',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.variant_id'     => 'nullable|exists:product_variants,id',
            'items.*.variant_name'   => 'nullable|string',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'discount'               => 'nullable|numeric|min:0',
            'tax'                    => 'nullable|numeric|min:0',
            'paid_amount'            => 'required|numeric|min:0',
            'payment_method'         => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($request, $sale, $branch) {
            $oldItems = $sale->saleItems()->with('product')->get();
            $newItems = $request->items;

            // Map old items by product_id and variant_id
            $oldMap = [];
            foreach ($oldItems as $old) {
                $vId = $old->product_variant_id ? (int) $old->product_variant_id : 0;
                $key = $old->product_id . '_' . $vId;
                if (!isset($oldMap[$key])) {
                    $oldMap[$key] = [
                        'product_id'         => $old->product_id,
                        'product_variant_id' => $old->product_variant_id,
                        'variant_name'       => $old->variant_name,
                        'unit_price'         => (float) $old->unit_price,
                        'quantity'           => 0,
                        'supplier_id'        => $old->supplier_id ?? $old->product?->supplier_id,
                        'product'            => $old->product,
                    ];
                }
                $oldMap[$key]['quantity'] += (int) $old->quantity;
            }

            // Map new items by product_id and variant_id
            $newMap = [];
            foreach ($newItems as $item) {
                $vId = !empty($item['variant_id']) ? (int) $item['variant_id'] : 0;
                $pId = (int) $item['product_id'];
                $key = $pId . '_' . $vId;
                if (!isset($newMap[$key])) {
                    $newMap[$key] = [
                        'product_id'         => $pId,
                        'product_variant_id' => !empty($item['variant_id']) ? (int) $item['variant_id'] : null,
                        'variant_name'       => $item['variant_name'] ?? null,
                        'unit_price'         => (float) $item['unit_price'],
                        'quantity'           => 0,
                    ];
                }
                $newMap[$key]['quantity'] += (int) $item['quantity'];
            }

            // 1. Identify deleted / reduced items -> create SaleReturn & pending SaleReturnItem
            // Do NOT immediately increment stock back to inventory.
            // Items remain in the Returns queue until sent/returned to the supplier, which restores stock.
            $returnedItems = [];
            $refundAmount = 0;

            foreach ($oldMap as $key => $old) {
                $newQty = isset($newMap[$key]) ? $newMap[$key]['quantity'] : 0;
                if ($newQty < $old['quantity']) {
                    $removedQty = $old['quantity'] - $newQty;
                    $lineSubtotal = round($removedQty * $old['unit_price'], 2);
                    $refundAmount += $lineSubtotal;

                    $supplierId = $old['supplier_id'];
                    if (!$supplierId && $old['product']) {
                        $supplierId = $old['product']->supplier_id;
                    }

                    $returnedItems[] = [
                        'product_id'         => $old['product_id'],
                        'supplier_id'        => $supplierId,
                        'product_variant_id' => $old['product_variant_id'],
                        'variant_name'       => $old['variant_name'],
                        'quantity'           => $removedQty,
                        'unit_price'         => $old['unit_price'],
                        'subtotal'           => $lineSubtotal,
                        'status'             => 'pending',
                        'notes'              => "Removed during edit of Invoice #{$sale->invoice_no}",
                    ];
                }
            }

            if (!empty($returnedItems)) {
                $saleReturn = SaleReturn::create([
                    'vendor_id'     => $sale->vendor_id,
                    'sale_id'       => $sale->id,
                    'reason'        => 'Item(s) removed during invoice edit',
                    'refund_amount' => $refundAmount,
                    'refund_method' => 'cash',
                    'created_by'    => auth()->id(),
                ]);

                foreach ($returnedItems as $retItem) {
                    $retItem['sale_return_id'] = $saleReturn->id;
                    SaleReturnItem::create($retItem);
                }
            }

            // 2. Identify new or increased items -> decrement stock & log out movement
            foreach ($newMap as $key => $new) {
                $oldQty = isset($oldMap[$key]) ? $oldMap[$key]['quantity'] : 0;
                if ($new['quantity'] > $oldQty) {
                    $addedQty = $new['quantity'] - $oldQty;
                    Product::withoutGlobalScopes()->where('id', $new['product_id'])
                        ->decrement('stock_qty', $addedQty);

                    if (!empty($new['product_variant_id'])) {
                        ProductVariant::where('id', $new['product_variant_id'])
                            ->decrement('stock_qty', $addedQty);
                    }

                    StockMovement::create([
                        'vendor_id'      => $sale->vendor_id,
                        'product_id'     => $new['product_id'],
                        'type'           => 'out',
                        'quantity'       => $addedQty,
                        'reference_type' => 'sale',
                        'reference_id'   => $sale->id,
                        'note'           => 'Added during invoice edit: ' . $sale->invoice_no,
                    ]);
                }
            }

            // 3. Clear old sale items and write current sale items
            $sale->saleItems()->delete();

            $subtotal = collect($newItems)->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $discount = $request->discount ?? 0;
            $tax      = $request->tax ?? 0;
            $total    = max(0, $subtotal - $discount + $tax);
            $paid     = $request->paid_amount;
            $due      = max(0, $total - $paid);
            $status   = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            $sale->update([
                'customer_id'    => $request->customer_id ?: null,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'paid_amount'    => $paid,
                'due_amount'     => $due,
                'payment_status' => $status,
                'payment_method' => $request->payment_method ?? $sale->payment_method,
            ]);

            foreach ($newItems as $item) {
                $variantName = $item['variant_name'] ?? null;
                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::find($item['variant_id']);
                    $variantName = $variant?->variant_name ?? $variantName;
                }

                $prod = Product::withoutGlobalScopes()->find($item['product_id']);

                SaleItem::create([
                    'sale_id'            => $sale->id,
                    'product_id'         => $item['product_id'],
                    'supplier_id'        => $prod?->supplier_id,
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'variant_name'       => $variantName,
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                    'subtotal'           => $item['quantity'] * $item['unit_price'],
                ]);
            }
        });

        return response()->json([
            'success'  => true,
            'redirect' => route('branch.sales.show', [$branch, $sale]),
        ]);
    }

    public function updateStatus(Request $request, Vendor $branch, Sale $sale)
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
            ]);
        }

        return back()->with('success', 'Order status updated.');
    }

    public function addPayment(Request $request, Vendor $branch, Sale $sale)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $sale->due_amount],
            'method' => 'required|string|max:50',
        ]);

        DB::transaction(function () use ($request, $sale, $branch) {
            Payment::create([
                'vendor_id' => $branch->id,
                'sale_id'   => $sale->id,
                'amount'    => $request->amount,
                'method'    => $request->method,
                'paid_at'   => now(),
            ]);

            $newPaid = $sale->paid_amount + $request->amount;
            $newDue  = max(0, $sale->total - $newPaid);
            $status  = $newDue <= 0 ? 'paid' : 'partial';

            $sale->update([
                'paid_amount'    => $newPaid,
                'due_amount'     => $newDue,
                'payment_status' => $status,
            ]);
        });

        $sale->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'due_amount'     => (float) $sale->due_amount,
                'paid_amount'    => (float) $sale->paid_amount,
                'payment_status' => $sale->payment_status,
            ]);
        }

        return back()->with('success', 'Payment of ৳' . number_format($request->amount, 2) . ' recorded.');
    }

    /**
     * Display multiple sales for batch printing (multiple orders per page)
     */
    public function bulkPrint(Request $request)
    {
        $rawIds = $request->input('ids');
        if (is_string($rawIds)) {
            $ids = array_filter(array_map('trim', explode(',', $rawIds)));
        } elseif (is_array($rawIds)) {
            $ids = array_filter($rawIds);
        } else {
            $ids = [];
        }

        if (empty($ids)) {
            return redirect()->back()->with('error', 'কোনো অর্ডার নির্বাচন করা হয়নি।');
        }

        $query = Sale::withoutGlobalScopes()
            ->with([
                'vendor',
                'customer',
                'createdBy',
                'saleItems.product',
                'saleItems.variant.color',
                'saleItems.variant.size',
                'notes.user',
            ])
            ->whereIn('id', $ids);

        $user = auth()->user();
        if ($user && !$user->hasRole('super-admin') && $user->vendor_id) {
            $query->where('vendor_id', $user->vendor_id);
        }

        $sales = $query->latest()->get();

        if ($sales->isEmpty()) {
            return redirect()->back()->with('error', 'নির্বাচিত অর্ডারগুলো পাওয়া যায়নি।');
        }

        // Generate SVG Barcodes for invoices
        $barcodes = [];
        try {
            if (class_exists(\Picqer\Barcode\BarcodeGeneratorSVG::class)) {
                $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                foreach ($sales as $sale) {
                    try {
                        $cleanCode = preg_replace('/[^A-Za-z0-9\-\_]/', '', $sale->invoice_no);
                        if ($cleanCode) {
                            $barcodes[$sale->id] = $generator->getBarcode($cleanCode, $generator::TYPE_CODE_128, 1.4, 30);
                        }
                    } catch (\Throwable $e) {
                        $barcodes[$sale->id] = null;
                    }
                }
            }
        } catch (\Throwable $e) {
            // In case of barcode generator error, barcode gracefully remains null
        }

        return view('sales.bulk-print', compact('sales', 'barcodes'));
    }
}
