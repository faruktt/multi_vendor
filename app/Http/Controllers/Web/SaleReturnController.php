<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaleReturnController extends Controller
{
    /**
     * Display list of returned items (both admin cross-branch and branch-specific).
     */
    public function index(Request $request, ...$args)
    {
        // Extract branch if passed in route (e.g. branch.returns.index)
        $branch = collect($args)->first(fn($a) => $a instanceof Vendor);
        if (!$branch && !empty($args[0]) && is_numeric($args[0])) {
            $branch = Vendor::find($args[0]);
        }

        $query = SaleReturnItem::with([
            'saleReturn.sale.customer',
            'saleReturn.vendor',
            'product.supplier',
            'product.vendor',
            'supplier',
            'productVariant',
            'returnedBy',
        ])->latest('id');

        if ($branch) {
            $query->whereHas('saleReturn', fn($q) => $q->where('vendor_id', $branch->id));
        } elseif ($request->filled('branch_id')) {
            $query->whereHas('saleReturn', fn($q) => $q->where('vendor_id', $request->branch_id));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $status = $request->input('status', 'all');
        if (in_array($status, ['pending', 'returned'])) {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('saleReturn.sale', function ($sq) use ($search) {
                    $sq->where('invoice_no', 'like', "%{$search}%")
                       ->orWhereHas('customer', function ($cq) use ($search) {
                           $cq->where('name', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%");
                       });
                })
                ->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('sku', 'like', "%{$search}%");
                })
                ->orWhere('variant_name', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Summary counts and amounts
        $baseCountQuery = SaleReturnItem::query();
        if ($branch) {
            $baseCountQuery->whereHas('saleReturn', fn($q) => $q->where('vendor_id', $branch->id));
        } elseif ($request->filled('branch_id')) {
            $baseCountQuery->whereHas('saleReturn', fn($q) => $q->where('vendor_id', $request->branch_id));
        }

        $totalCount     = (clone $baseCountQuery)->count();
        $totalAmount    = (clone $baseCountQuery)->sum('subtotal');
        $pendingCount   = (clone $baseCountQuery)->where('status', 'pending')->count();
        $pendingAmount  = (clone $baseCountQuery)->where('status', 'pending')->sum('subtotal');
        $returnedCount  = (clone $baseCountQuery)->where('status', 'returned')->count();
        $returnedAmount = (clone $baseCountQuery)->where('status', 'returned')->sum('subtotal');

        $returnItems = $query->paginate(20)->withQueryString();
        $suppliers   = Supplier::orderBy('name')->get();
        $branches    = Vendor::orderBy('name')->get();

        return view('returns.index', compact(
            'returnItems',
            'branch',
            'suppliers',
            'branches',
            'status',
            'totalCount',
            'totalAmount',
            'pendingCount',
            'pendingAmount',
            'returnedCount',
            'returnedAmount'
        ));
    }

    /**
     * Process return of an item:
     * - Restores product & variant stock back to inventory.
     * - Records stock movement (attributing to Supplier or Branch).
     * - Marks the item status as 'returned'.
     */
    public function processReturn(Request $request, ...$args)
    {
        $item = collect($args)->first(fn($a) => $a instanceof SaleReturnItem);
        if (!$item) {
            $id = end($args);
            $item = SaleReturnItem::with(['saleReturn.sale', 'saleReturn.vendor', 'product.supplier', 'product.vendor', 'supplier'])->findOrFail($id);
        } else {
            $item->load(['saleReturn.sale', 'saleReturn.vendor', 'product.supplier', 'product.vendor', 'supplier']);
        }

        if ($item->status === 'returned') {
            return $this->respond($request, false, 'This item has already been restocked/returned.');
        }

        $supplierId = $request->input('supplier_id', $item->supplier_id) ?: $item->product?->supplier_id;
        $supplier = $supplierId ? Supplier::find($supplierId) : null;
        $branch = $item->saleReturn?->vendor ?: $item->product?->vendor;
        $sourceLabel = $supplier ? "Supplier: {$supplier->name}" : ($branch ? "Branch: {$branch->name}" : 'Stock');

        DB::transaction(function () use ($item, $supplierId, $supplier, $branch, $sourceLabel) {
            // 1. Increment product stock
            Product::withoutGlobalScopes()->where('id', $item->product_id)
                ->increment('stock_qty', $item->quantity);

            // 2. Increment variant stock if applicable
            if ($item->product_variant_id) {
                ProductVariant::where('id', $item->product_variant_id)
                    ->increment('stock_qty', $item->quantity);
            }

            // 3. Log stock movement
            $vendorId = $item->saleReturn?->vendor_id ?? $item->product?->vendor_id ?? auth()->user()->vendor_id ?? 1;

            StockMovement::create([
                'vendor_id'      => $vendorId,
                'product_id'     => $item->product_id,
                'type'           => 'in',
                'quantity'       => $item->quantity,
                'reference_type' => $supplier ? 'supplier_return' : 'partial_return',
                'reference_id'   => $item->id,
                'note'           => "Restocked from partial return ({$sourceLabel})" . ($item->saleReturn?->sale ? " from Inv #{$item->saleReturn->sale->invoice_no}" : ''),
            ]);

            // 4. Update return item status
            $item->update([
                'status'      => 'returned',
                'supplier_id' => $supplierId,
                'returned_at' => now(),
                'returned_by' => auth()->id(),
            ]);
        });

        $msg = "Successfully restocked {$item->quantity} unit(s) of \"{$item->product?->name}\" ({$sourceLabel}) to inventory!";

        return $this->respond($request, true, $msg);
    }

    /**
     * Bulk process multiple return items.
     */
    public function bulkProcess(Request $request)
    {
        $request->validate([
            'ids'           => 'required|array|min:1',
            'ids.*'         => 'required|exists:sale_return_items,id',
            'supplier_id'   => 'nullable|exists:suppliers,id',
        ]);

        $items = SaleReturnItem::with(['saleReturn.sale', 'saleReturn.vendor', 'product.supplier', 'product.vendor', 'supplier'])
            ->whereIn('id', $request->ids)
            ->where('status', 'pending')
            ->get();

        if ($items->isEmpty()) {
            return $this->respond($request, false, 'No pending return items found to process.');
        }

        $processedCount = 0;

        DB::transaction(function () use ($items, $request, &$processedCount) {
            foreach ($items as $item) {
                $supplierId = $request->input('supplier_id', $item->supplier_id) ?: $item->product?->supplier_id;

                Product::withoutGlobalScopes()->where('id', $item->product_id)
                    ->increment('stock_qty', $item->quantity);

                if ($item->product_variant_id) {
                    ProductVariant::where('id', $item->product_variant_id)
                        ->increment('stock_qty', $item->quantity);
                }

                $supplier = $supplierId ? Supplier::find($supplierId) : null;
                $branch = $item->saleReturn?->vendor ?: $item->product?->vendor;
                $sourceLabel = $supplier ? "Supplier: {$supplier->name}" : ($branch ? "Branch: {$branch->name}" : 'Stock');
                $vendorId = $item->saleReturn?->vendor_id ?? $item->product?->vendor_id ?? auth()->user()->vendor_id ?? 1;

                StockMovement::create([
                    'vendor_id'      => $vendorId,
                    'product_id'     => $item->product_id,
                    'type'           => 'in',
                    'quantity'       => $item->quantity,
                    'reference_type' => $supplier ? 'supplier_return' : 'partial_return',
                    'reference_id'   => $item->id,
                    'note'           => "Bulk restocked from partial return ({$sourceLabel})" . ($item->saleReturn?->sale ? " from Inv #{$item->saleReturn->sale->invoice_no}" : ''),
                ]);

                $item->update([
                    'status'      => 'returned',
                    'supplier_id' => $supplierId,
                    'returned_at' => now(),
                    'returned_by' => auth()->id(),
                ]);

                $processedCount++;
            }
        });

        $msg = "Successfully restocked {$processedCount} item(s) to inventory!";
        return $this->respond($request, true, $msg);
    }

    /**
     * Update supplier assigned to a return item.
     */
    public function updateSupplier(Request $request, ...$args)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        $item = collect($args)->first(fn($a) => $a instanceof SaleReturnItem);
        if (!$item) {
            $id = end($args);
            $item = SaleReturnItem::findOrFail($id);
        }

        $item->update([
            'supplier_id' => $request->supplier_id,
        ]);

        $supplier = Supplier::find($request->supplier_id);
        $msg = "Supplier updated to {$supplier?->name} successfully.";

        return $this->respond($request, true, $msg, ['supplier_name' => $supplier?->name]);
    }

    /**
     * Legacy/Modal Return directly from Invoice show view.
     */
    public function store(Request $request, Vendor $branch, Sale $sale)
    {
        $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.sale_item_id'  => 'required|exists:sale_items,id',
            'items.*.quantity'      => 'required|integer|min:1',
            'reason'                => 'nullable|string|max:500',
            'refund_method'         => ['required', 'string', Rule::in(PaymentMethod::where('is_active', true)->pluck('key'))],
        ]);

        $sale->load('saleItems.returnItems');

        $refundAmount = 0;
        $lines = [];

        foreach ($request->items as $row) {
            $saleItem = $sale->saleItems->firstWhere('id', (int) $row['sale_item_id']);

            if (!$saleItem) {
                return $this->fail($request, 'One of the selected items does not belong to this invoice.');
            }

            $alreadyReturned = $saleItem->returnItems->sum('quantity');
            $remaining = $saleItem->quantity - $alreadyReturned;

            if ($row['quantity'] > $remaining) {
                return $this->fail($request, "Cannot return {$row['quantity']} of \"{$saleItem->product?->name}\" — only {$remaining} left returnable.");
            }

            $subtotal = $saleItem->unit_price * $row['quantity'];
            $refundAmount += $subtotal;

            $lines[] = [
                'sale_item' => $saleItem,
                'quantity'  => (int) $row['quantity'],
                'subtotal'  => $subtotal,
            ];
        }

        $saleReturn = DB::transaction(function () use ($request, $branch, $sale, $lines, $refundAmount) {
            $saleReturn = SaleReturn::create([
                'vendor_id'     => $branch->id,
                'sale_id'       => $sale->id,
                'reason'        => $request->reason,
                'refund_amount' => $refundAmount,
                'refund_method' => $request->refund_method,
                'created_by'    => auth()->id(),
            ]);

            foreach ($lines as $line) {
                $saleItem = $line['sale_item'];

                $saleReturn->items()->create([
                    'sale_item_id'       => $saleItem->id,
                    'product_id'         => $saleItem->product_id,
                    'supplier_id'        => $saleItem->supplier_id ?? $saleItem->product?->supplier_id,
                    'product_variant_id' => $saleItem->product_variant_id,
                    'variant_name'       => $saleItem->variant_name,
                    'quantity'           => $line['quantity'],
                    'unit_price'         => $saleItem->unit_price,
                    'subtotal'           => $line['subtotal'],
                    'status'             => 'returned',
                    'returned_at'        => now(),
                    'returned_by'        => auth()->id(),
                ]);

                Product::withoutGlobalScopes()->where('id', $saleItem->product_id)
                    ->increment('stock_qty', $line['quantity']);

                if ($saleItem->product_variant_id) {
                    ProductVariant::where('id', $saleItem->product_variant_id)
                        ->increment('stock_qty', $line['quantity']);
                }

                StockMovement::create([
                    'vendor_id'      => $branch->id,
                    'product_id'     => $saleItem->product_id,
                    'type'           => 'in',
                    'quantity'       => $line['quantity'],
                    'reference_type' => 'sale_return',
                    'reference_id'   => $saleReturn->id,
                    'note'           => 'Return: ' . $sale->invoice_no,
                ]);
            }

            $total = max(0, $sale->total - $refundAmount);
            if ($sale->due_amount >= $refundAmount) {
                $due  = $sale->due_amount - $refundAmount;
                $paid = $sale->paid_amount;
            } else {
                $remainder = $refundAmount - $sale->due_amount;
                $due  = 0;
                $paid = max(0, $sale->paid_amount - $remainder);
            }
            $status = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            $sale->update([
                'total'          => $total,
                'due_amount'     => $due,
                'paid_amount'    => $paid,
                'payment_status' => $status,
            ]);

            return $saleReturn;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'refund_amount'  => (float) $saleReturn->refund_amount,
                'total'          => (float) $sale->fresh()->total,
                'due_amount'     => (float) $sale->fresh()->due_amount,
                'paid_amount'    => (float) $sale->fresh()->paid_amount,
                'payment_status' => $sale->fresh()->payment_status,
            ]);
        }

        return back()->with('success', 'Return recorded and stock updated.');
    }

    private function respond(Request $request, bool $success, string $message, array $extra = [])
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(array_merge([
                'success' => $success,
                'message' => $message,
            ], $extra), $success ? 200 : 422);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }

    private function fail(Request $request, string $message)
    {
        return $this->respond($request, false, $message);
    }
}
