<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    public function index()
    {
        return response()->json(
            Purchase::with(['supplier', 'createdBy'])
                ->withCount('items')
                ->latest()
                ->get()
        );
    }

    public function show(Purchase $purchase)
    {
        return response()->json(
            $purchase->load(['supplier', 'items.product', 'createdBy'])
        );
    }

    public function store(Request $request)
    {
        $vendorId = $request->user()->vendor_id;

        $validated = $request->validate([
            'supplier_id'          => 'nullable|exists:suppliers,id',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_cost'    => 'required|numeric|min:0',
            'discount'             => 'nullable|numeric|min:0',
            'paid_amount'          => 'required|numeric|min:0',
            'payment_method'       => 'nullable|string|max:50',
            'note'                 => 'nullable|string',
        ]);

        if (!$vendorId) {
            $vendorId = Product::withoutGlobalScopes()
                ->where('id', $validated['items'][0]['product_id'])
                ->value('vendor_id');
        }

        return DB::transaction(function () use ($validated, $vendorId, $request) {
            $subtotal = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['unit_cost']);
            $discount = $validated['discount'] ?? 0;
            $total    = max(0, $subtotal - $discount);
            $paid     = $validated['paid_amount'];
            $due      = max(0, $total - $paid);
            $status   = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            $purchase = Purchase::create([
                'vendor_id'      => $vendorId,
                'supplier_id'    => $validated['supplier_id'] ?? null,
                'created_by'     => $request->user()->id,
                'invoice_no'     => 'PUR-' . strtoupper(Str::random(8)),
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'paid_amount'    => $paid,
                'due_amount'     => $due,
                'payment_status' => $status,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'note'           => $validated['note'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::withoutGlobalScopes()->find($item['product_id']);

                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_id'   => $item['product_id'],
                    'product_name' => $product->name,
                    'quantity'     => $item['quantity'],
                    'unit_cost'    => $item['unit_cost'],
                    'subtotal'     => $item['quantity'] * $item['unit_cost'],
                ]);

                Product::withoutGlobalScopes()
                    ->where('id', $item['product_id'])
                    ->increment('stock_qty', $item['quantity']);

                StockMovement::create([
                    'vendor_id'      => $vendorId,
                    'product_id'     => $item['product_id'],
                    'type'           => 'in',
                    'quantity'       => $item['quantity'],
                    'reference_type' => 'purchase',
                    'reference_id'   => $purchase->id,
                    'note'           => 'Purchase: ' . $purchase->invoice_no,
                ]);
            }

            return response()->json($purchase->load(['items.product', 'supplier']), 201);
        });
    }

    public function update(Request $request, Purchase $purchase)
    {
        $vendorId = $request->user()->vendor_id ?? $purchase->vendor_id;

        $validated = $request->validate([
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

        return DB::transaction(function () use ($validated, $purchase, $vendorId) {
            // Reverse old stock
            foreach ($purchase->items as $oldItem) {
                if ($oldItem->product_id) {
                    Product::withoutGlobalScopes()
                        ->where('id', $oldItem->product_id)
                        ->decrement('stock_qty', $oldItem->quantity);
                }
            }

            StockMovement::where('reference_type', 'purchase')
                ->where('reference_id', $purchase->id)
                ->delete();

            $purchase->items()->delete();

            $subtotal = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['unit_cost']);
            $discount = $validated['discount'] ?? 0;
            $total    = max(0, $subtotal - $discount);
            $paid     = $validated['paid_amount'];
            $due      = max(0, $total - $paid);
            $status   = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            $purchase->update([
                'supplier_id'    => $validated['supplier_id'] ?? null,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'paid_amount'    => $paid,
                'due_amount'     => $due,
                'payment_status' => $status,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'note'           => $validated['note'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::withoutGlobalScopes()->find($item['product_id']);

                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_id'   => $item['product_id'],
                    'product_name' => $product?->name ?? 'Unknown',
                    'quantity'     => $item['quantity'],
                    'unit_cost'    => $item['unit_cost'],
                    'subtotal'     => $item['quantity'] * $item['unit_cost'],
                ]);

                if ($product) {
                    Product::withoutGlobalScopes()
                        ->where('id', $item['product_id'])
                        ->increment('stock_qty', $item['quantity']);

                    StockMovement::create([
                        'vendor_id'      => $vendorId,
                        'product_id'     => $item['product_id'],
                        'type'           => 'in',
                        'quantity'       => $item['quantity'],
                        'reference_type' => 'purchase',
                        'reference_id'   => $purchase->id,
                        'note'           => 'Purchase (edited): ' . $purchase->invoice_no,
                    ]);
                }
            }

            return response()->json($purchase->fresh()->load(['items.product', 'supplier']));
        });
    }

    public function updatePayment(Request $request, Purchase $purchase)
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $paid   = min($request->paid_amount, $purchase->total);
        $due    = max(0, $purchase->total - $paid);
        $status = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

        $purchase->update([
            'paid_amount'    => $paid,
            'due_amount'     => $due,
            'payment_status' => $status,
        ]);

        return response()->json($purchase->fresh()->load(['supplier', 'items.product']));
    }
}
