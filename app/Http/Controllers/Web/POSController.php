<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class POSController extends Controller
{
    public function index(Vendor $branch)
    {
        $categories = $branch->categories()->orderBy('name')->get();
        $customers  = $branch->customers()->orderBy('name')->get();
        $paymentMethods = PaymentMethod::options();

        return view('pos.index', compact('branch', 'categories', 'customers', 'paymentMethods'));
    }

    public function products(Request $request, Vendor $branch)
    {
        $query = Product::withoutGlobalScopes()->with(['category', 'variants']);

        if ($branch->is_warehouse) {
            // Used by the Warehouse's Purchase form to search products entered at any branch
            // (the Warehouse buys stock of them, regardless of that branch's current stock level).
            $query->where('vendor_id', '!=', $branch->id)->with('vendor');
        } else {
            $query->where('vendor_id', $branch->id)->where('stock_qty', '>', 0);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', $request->search)
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if (!$branch->is_warehouse && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->limit(50)->get()->map(function ($p) use ($branch) {
            return [
                'id'         => $p->id,
                'name'       => $p->name,
                'price'      => $p->price,
                'cost_price' => $p->cost_price,
                'stock_qty'  => $p->stock_qty,
                'unit'       => $p->unit,
                'barcode'    => $p->barcode,
                'category'   => $p->category?->name,
                'branch'     => $branch->is_warehouse ? $p->vendor?->name : null,
                'image'      => $p->image ? Storage::disk('uploads')->url($p->image) : null,
                'variants'   => $p->variants->map(fn($v) => [
                    'id'           => $v->id,
                    'variant_name' => $v->variant_name,
                    'price'        => $v->price ?? $p->price,
                    'stock_qty'    => $v->stock_qty,
                ]),
            ];
        });

        return response()->json($products);
    }

    public function customers(Vendor $branch)
    {
        $customers = $branch->customers()->orderBy('name')->get(['id', 'name', 'phone']);
        return response()->json($customers);
    }

    public function storeSale(Request $request, Vendor $branch)
    {
        $validated = $request->validate([
            'customer_id'                => ['nullable', Rule::exists('customers', 'id')->where('vendor_id', $branch->id)],
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => ['required', Rule::exists('products', 'id')->where('vendor_id', $branch->id)],
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity'           => 'required|integer|min:1',
            'items.*.unit_price'         => 'required|numeric|min:0',
            'discount'                   => 'nullable|numeric|min:0',
            'tax'                        => 'nullable|numeric|min:0',
            'paid_amount'                => 'required|numeric|min:0',
            'payment_method'             => 'nullable|string|max:50',
        ]);

        $sale = DB::transaction(function () use ($validated, $branch) {
            $subtotal = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $discount = $validated['discount'] ?? 0;
            $tax      = $validated['tax'] ?? 0;
            $total    = $subtotal - $discount + $tax;
            $paid     = $validated['paid_amount'];
            $due      = max(0, $total - $paid);
            $status   = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            $sale = Sale::create([
                'vendor_id'      => $branch->id,
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
                'order_status'   => 'delivered',
                'channel'        => 'pos',
                'created_by'     => auth()->id(),
            ]);

            if ($paid > 0) {
                Payment::create([
                    'vendor_id' => $branch->id,
                    'sale_id'   => $sale->id,
                    'amount'    => $paid,
                    'method'    => $validated['payment_method'] ?? 'cash',
                    'paid_at'   => now(),
                ]);
            }

            foreach ($validated['items'] as $item) {
                $variantName = null;
                if (!empty($item['product_variant_id'])) {
                    $variant     = ProductVariant::find($item['product_variant_id']);
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
                    'vendor_id'      => $branch->id,
                    'product_id'     => $item['product_id'],
                    'type'           => 'out',
                    'quantity'       => $item['quantity'],
                    'reference_type' => 'sale',
                    'reference_id'   => $sale->id,
                    'note'           => 'Sale: ' . $sale->invoice_no,
                ]);
            }

            return $sale;
        });

        return response()->json([
            'success'    => true,
            'invoice_no' => $sale->invoice_no,
            'sale_id'    => $sale->id,
            'branch_id'  => $branch->id,
            'total'      => (float) $sale->total,
            'paid'       => (float) $sale->paid_amount,
            'due'        => (float) $sale->due_amount,
            'change'     => (float) max(0, $sale->paid_amount - $sale->total),
        ]);
    }
}
