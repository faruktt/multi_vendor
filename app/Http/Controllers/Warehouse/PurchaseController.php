<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    /** Warehouse has no category management UI — silently file its own stock clones under one bucket category. */
    private function warehouseCategoryId(Vendor $warehouse): int
    {
        return Category::withoutGlobalScopes()->firstOrCreate(
            ['vendor_id' => $warehouse->id, 'name' => 'General']
        )->id;
    }

    /**
     * Products are entered at the branches, not the Warehouse. The Warehouse buys stock of an
     * existing branch product from a supplier — the first purchase auto-clones it into the
     * Warehouse's own stock pool (mirrors how Transfer clones a Warehouse product into a branch).
     */
    private function warehouseCloneOf(Vendor $warehouse, Product $original): Product
    {
        if ($original->vendor_id === $warehouse->id) {
            return $original;
        }

        return Product::withoutGlobalScopes()
            ->where('vendor_id', $warehouse->id)
            ->where('source_product_id', $original->id)
            ->first() ?? Product::create([
                'vendor_id'          => $warehouse->id,
                'category_id'        => $this->warehouseCategoryId($warehouse),
                'source_product_id'  => $original->id,
                'name'               => $original->name,
                'sku'                => 'SKU-' . strtoupper(Str::random(8)),
                'description'        => $original->description,
                'image'              => $original->image,
                'price'              => $original->price,
                'cost_price'         => $original->cost_price,
                'stock_qty'          => 0,
                'unit'               => $original->unit,
                'status'             => 'active',
            ]);
    }

    public function index(Request $request, Vendor $branch)
    {
        $query = Purchase::withoutGlobalScopes()
            ->with(['supplier', 'createdBy', 'items.product'])
            ->withCount('items')
            ->where('vendor_id', $branch->id)
            ->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_no', 'like', '%' . $request->search . '%')
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        $purchases = $query->paginate(20)->withQueryString();
        $suppliers = $branch->suppliers()->orderBy('name')->get();
        $paymentMethods = PaymentMethod::options();

        return view('warehouse.purchases.index', compact('branch', 'purchases', 'suppliers', 'paymentMethods'));
    }

    public function create(Vendor $branch)
    {
        $suppliers = $branch->suppliers()->orderBy('name')->get();
        $products  = Product::withoutGlobalScopes()
            ->whereNull('source_product_id')
            ->with(['variants', 'vendor', 'category'])
            ->orderBy('name')
            ->get();
        $paymentMethods = PaymentMethod::options();
        return view('warehouse.purchases.form', compact('branch', 'suppliers', 'products', 'paymentMethods'));
    }

    public function store(Request $request, Vendor $branch)
    {
        $request->validate([
            'supplier_id'         => 'nullable|exists:suppliers,id',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_cost'   => 'required|numeric|min:0',
            'discount'            => 'nullable|numeric|min:0',
            'paid_amount'         => 'required|numeric|min:0',
            'payment_method'      => 'nullable|string|max:50',
            'note'                => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $branch) {
            $subtotal = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_cost']);
            $discount = $request->discount ?? 0;
            $total    = max(0, $subtotal - $discount);
            $paid     = min($request->paid_amount, $total);
            $due      = max(0, $total - $paid);
            $status   = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            $purchase = Purchase::create([
                'vendor_id'      => $branch->id,
                'supplier_id'    => $request->supplier_id,
                'created_by'     => auth()->id(),
                'invoice_no'     => 'PUR-' . strtoupper(Str::random(8)),
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'paid_amount'    => $paid,
                'due_amount'     => $due,
                'payment_status' => $status,
                'payment_method' => $request->payment_method ?? 'cash',
                'note'           => $request->note,
            ]);

            foreach ($request->items as $item) {
                $original = Product::withoutGlobalScopes()->find($item['product_id']);
                $clone    = $this->warehouseCloneOf($branch, $original);

                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_id'   => $clone->id,
                    'product_name' => $original->name,
                    'quantity'     => $item['quantity'],
                    'unit_cost'    => $item['unit_cost'],
                    'subtotal'     => $item['quantity'] * $item['unit_cost'],
                ]);

                Product::withoutGlobalScopes()->where('id', $clone->id)->increment('stock_qty', $item['quantity']);

                StockMovement::create([
                    'vendor_id'      => $branch->id,
                    'product_id'     => $clone->id,
                    'type'           => 'in',
                    'quantity'       => $item['quantity'],
                    'reference_type' => 'purchase',
                    'reference_id'   => $purchase->id,
                    'note'           => 'Purchase: ' . $purchase->invoice_no,
                ]);
            }
        });

        return redirect()->route('admin.warehouse.purchases.index')->with('success', 'Purchase order added successfully.');
    }

    public function show(Vendor $branch, Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'items.returnItems', 'createdBy', 'returns.items.product']);
        $paymentMethods = PaymentMethod::options();
        return view('warehouse.purchases.show', compact('branch', 'purchase', 'paymentMethods'));
    }

    public function edit(Vendor $branch, Purchase $purchase)
    {
        $purchase->load('items.product');
        $suppliers = $branch->suppliers()->orderBy('name')->get();
        $products  = Product::withoutGlobalScopes()
            ->whereNull('source_product_id')
            ->with(['variants', 'vendor', 'category'])
            ->orderBy('name')
            ->get();
        $paymentMethods = PaymentMethod::options();

        // Stored items reference the Warehouse's own clone — map each back to the original
        // branch product id so the (branch-product-only) dropdown can preselect it.
        foreach ($purchase->items as $item) {
            $item->display_product_id = ($item->product && $item->product->source_product_id)
                ? $item->product->source_product_id
                : $item->product_id;
        }

        return view('warehouse.purchases.form', compact('branch', 'purchase', 'suppliers', 'products', 'paymentMethods'));
    }

    public function update(Request $request, Vendor $branch, Purchase $purchase)
    {
        $request->validate([
            'supplier_id'         => 'nullable|exists:suppliers,id',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_cost'   => 'required|numeric|min:0',
            'discount'            => 'nullable|numeric|min:0',
            'paid_amount'         => 'required|numeric|min:0',
            'payment_method'      => 'nullable|string|max:50',
            'note'                => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $branch, $purchase) {
            foreach ($purchase->items as $oldItem) {
                if ($oldItem->product_id) {
                    Product::withoutGlobalScopes()->where('id', $oldItem->product_id)->decrement('stock_qty', $oldItem->quantity);
                }
            }

            StockMovement::where('reference_type', 'purchase')->where('reference_id', $purchase->id)->delete();
            $purchase->items()->delete();

            $subtotal = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_cost']);
            $discount = $request->discount ?? 0;
            $total    = max(0, $subtotal - $discount);
            $paid     = min($request->paid_amount, $total);
            $due      = max(0, $total - $paid);
            $status   = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            $purchase->update([
                'supplier_id'    => $request->supplier_id,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'paid_amount'    => $paid,
                'due_amount'     => $due,
                'payment_status' => $status,
                'payment_method' => $request->payment_method ?? 'cash',
                'note'           => $request->note,
            ]);

            foreach ($request->items as $item) {
                $original = Product::withoutGlobalScopes()->find($item['product_id']);
                $clone    = $original ? $this->warehouseCloneOf($branch, $original) : null;

                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_id'   => $clone?->id,
                    'product_name' => $original?->name ?? 'Unknown',
                    'quantity'     => $item['quantity'],
                    'unit_cost'    => $item['unit_cost'],
                    'subtotal'     => $item['quantity'] * $item['unit_cost'],
                ]);

                if ($clone) {
                    Product::withoutGlobalScopes()->where('id', $clone->id)->increment('stock_qty', $item['quantity']);
                    StockMovement::create([
                        'vendor_id'      => $branch->id,
                        'product_id'     => $clone->id,
                        'type'           => 'in',
                        'quantity'       => $item['quantity'],
                        'reference_type' => 'purchase',
                        'reference_id'   => $purchase->id,
                        'note'           => 'Purchase (edited): ' . $purchase->invoice_no,
                    ]);
                }
            }
        });

        return redirect()->route('admin.warehouse.purchases.show', $purchase)->with('success', 'Purchase updated successfully.');
    }

    public function updatePayment(Request $request, Vendor $branch, Purchase $purchase)
    {
        $request->validate([
            'additional_amount' => 'required|numeric|min:0',
            'payment_method'    => 'nullable|string|max:50',
        ]);

        $additional = min($request->additional_amount, $purchase->due_amount);
        $paid       = $purchase->paid_amount + $additional;
        $due        = max(0, $purchase->total - $paid);
        $status     = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

        $purchase->update([
            'paid_amount'    => $paid,
            'due_amount'     => $due,
            'payment_status' => $status,
            'payment_method' => $request->payment_method ?? $purchase->payment_method,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'paid_amount'    => $paid,
                'due_amount'     => $due,
                'payment_status' => $status,
            ]);
        }

        return back()->with('success', 'Payment updated.');
    }

    public function destroy(Vendor $branch, Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                if ($item->product_id) {
                    Product::withoutGlobalScopes()->where('id', $item->product_id)->decrement('stock_qty', $item->quantity);
                }
            }
            StockMovement::where('reference_type', 'purchase')->where('reference_id', $purchase->id)->delete();
            $purchase->items()->delete();
            $purchase->delete();
        });

        return redirect()->route('admin.warehouse.purchases.index')->with('success', 'Purchase deleted.');
    }
}
