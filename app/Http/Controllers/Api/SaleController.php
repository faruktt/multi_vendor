<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function index()
    {
        return response()->json(
            Sale::with(['customer', 'saleItems.product', 'saleItems.variant', 'createdBy'])
                ->latest()
                ->get()
        );
    }

    public function store(Request $request)
    {
        $vendorId = $request->user()->vendor_id;

        // Super-admin has vendor_id = null; skip vendor-scoped exists check in that case.
        $productRule  = $vendorId
            ? ['required', Rule::exists('products', 'id')->where('vendor_id', $vendorId)]
            : 'required|exists:products,id';
        $customerRule = $vendorId
            ? ['nullable', Rule::exists('customers', 'id')->where('vendor_id', $vendorId)]
            : 'nullable|exists:customers,id';

        $validated = $request->validate([
            'customer_id'                => $customerRule,
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => $productRule,
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity'           => 'required|integer|min:1',
            'items.*.unit_price'         => 'required|numeric|min:0',
            'discount'                   => 'nullable|numeric|min:0',
            'tax'                        => 'nullable|numeric|min:0',
            'paid_amount'                => 'required|numeric|min:0',
            'payment_method'             => 'nullable|string|max:50',
        ]);

        // For super-admin (vendor_id = null), derive vendor from the first product.
        if (!$vendorId) {
            $vendorId = Product::withoutGlobalScopes()
                ->where('id', $validated['items'][0]['product_id'])
                ->value('vendor_id');
        }

        return DB::transaction(function () use ($validated, $vendorId, $request) {
            $subtotal = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $discount = $validated['discount'] ?? 0;
            $tax      = $validated['tax'] ?? 0;
            $total    = $subtotal - $discount + $tax;
            $paid     = $validated['paid_amount'];
            $due      = max(0, $total - $paid);
            $status   = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            $sale = Sale::create([
                'vendor_id'      => $vendorId,
                'customer_id'    => $validated['customer_id'] ?? null,
                'invoice_no'     => Sale::generateInvoiceNo('INV'),
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'paid_amount'    => $paid,
                'due_amount'     => $due,
                'payment_status' => $status,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'created_by'     => $request->user()->id,
            ]);

            foreach ($validated['items'] as $item) {
                $variantName = null;
                if (!empty($item['product_variant_id'])) {
                    $variant = ProductVariant::find($item['product_variant_id']);
                    $variantName = $variant?->variant_name;
                    $variant?->decrement('stock_qty', $item['quantity']);
                }

                SaleItem::create([
                    'sale_id'            => $sale->id,
                    'product_id'         => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'variant_name'       => $variantName,
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                    'subtotal'           => $item['quantity'] * $item['unit_price'],
                ]);

                Product::withoutGlobalScopes()->where('id', $item['product_id'])
                    ->decrement('stock_qty', $item['quantity']);

                StockMovement::create([
                    'vendor_id'      => $vendorId,
                    'product_id'     => $item['product_id'],
                    'type'           => 'out',
                    'quantity'       => $item['quantity'],
                    'reference_type' => 'sale',
                    'reference_id'   => $sale->id,
                    'note'           => 'Sale: ' . $sale->invoice_no,
                ]);
            }

            return response()->json($sale->load(['saleItems.product', 'saleItems.variant', 'customer']), 201);
        });
    }

    public function show(Sale $sale)
    {
        return response()->json(
            $sale->load(['saleItems.product', 'saleItems.variant', 'customer', 'createdBy'])
        );
    }

    public function updateStatus(Request $request, Sale $sale)
    {
        $request->validate([
            'order_status' => ['required', Rule::in(OrderStatus::pluck('key'))],
        ]);

        $sale->update(['order_status' => $request->order_status]);

        return response()->json(['id' => $sale->id, 'order_status' => $sale->order_status]);
    }

    public function update(Request $request, Sale $sale)
    {
        $vendorId = $request->user()->vendor_id;

        $productRule  = $vendorId
            ? ['required', Rule::exists('products', 'id')->where('vendor_id', $vendorId)]
            : 'required|exists:products,id';
        $customerRule = $vendorId
            ? ['nullable', Rule::exists('customers', 'id')->where('vendor_id', $vendorId)]
            : 'nullable|exists:customers,id';

        $validated = $request->validate([
            'customer_id'                => $customerRule,
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => $productRule,
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity'           => 'required|integer|min:1',
            'items.*.unit_price'         => 'required|numeric|min:0',
            'discount'                   => 'nullable|numeric|min:0',
            'tax'                        => 'nullable|numeric|min:0',
            'paid_amount'                => 'required|numeric|min:0',
            'payment_method'             => 'nullable|string|max:50',
        ]);

        return DB::transaction(function () use ($validated, $sale) {
            // 1. Restore stock from old items
            foreach ($sale->saleItems as $old) {
                Product::withoutGlobalScopes()
                    ->where('id', $old->product_id)
                    ->increment('stock_qty', $old->quantity);

                if ($old->product_variant_id) {
                    ProductVariant::where('id', $old->product_variant_id)
                        ->increment('stock_qty', $old->quantity);
                }
            }

            // 2. Delete old items + stock movements
            $sale->saleItems()->delete();
            StockMovement::where('reference_type', 'sale')->where('reference_id', $sale->id)->delete();

            // 3. Recalculate totals
            $subtotal = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $discount = $validated['discount'] ?? 0;
            $tax      = $validated['tax'] ?? 0;
            $total    = $subtotal - $discount + $tax;
            $paid     = $validated['paid_amount'];
            $due      = max(0, $total - $paid);
            $status   = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            // 4. Update sale record
            $sale->update([
                'customer_id'    => $validated['customer_id'] ?? null,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'paid_amount'    => $paid,
                'due_amount'     => $due,
                'payment_status' => $status,
                'payment_method' => $validated['payment_method'] ?? $sale->payment_method,
            ]);

            // 5. Create new items and deduct stock
            foreach ($validated['items'] as $item) {
                $variantName = null;
                if (!empty($item['product_variant_id'])) {
                    $variant = ProductVariant::find($item['product_variant_id']);
                    $variantName = $variant?->variant_name;
                    $variant?->decrement('stock_qty', $item['quantity']);
                }

                SaleItem::create([
                    'sale_id'            => $sale->id,
                    'product_id'         => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'variant_name'       => $variantName,
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                    'subtotal'           => $item['quantity'] * $item['unit_price'],
                ]);

                Product::withoutGlobalScopes()->where('id', $item['product_id'])
                    ->decrement('stock_qty', $item['quantity']);

                StockMovement::create([
                    'vendor_id'      => $sale->vendor_id,
                    'product_id'     => $item['product_id'],
                    'type'           => 'out',
                    'quantity'       => $item['quantity'],
                    'reference_type' => 'sale',
                    'reference_id'   => $sale->id,
                    'note'           => 'Invoice edit: ' . $sale->invoice_no,
                ]);
            }

            return response()->json($sale->fresh()->load(['saleItems.product', 'saleItems.variant', 'customer']));
        });
    }
}
