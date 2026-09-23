<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferController extends Controller
{
    public function index(Vendor $branch)
    {
        $warehouse = $branch;

        // Named distinctly from the "branches" the global view composer injects everywhere
        // (the full vendor list, for the sidebar) — this is specifically the transfer-eligible ones.
        $toBranches = Vendor::where('status', 'active')->where('is_warehouse', false)->orderBy('name')->get();

        $products = Product::withoutGlobalScopes()
            ->where('vendor_id', $warehouse->id)
            ->where('stock_qty', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'vendor_id', 'stock_qty']);

        $recentTransfers = StockMovement::withoutGlobalScopes()
            ->where('vendor_id', $warehouse->id)
            ->where('reference_type', 'transfer')
            ->where('type', 'out')
            ->with(['product'])
            ->latest()
            ->take(30)
            ->get();

        return view('warehouse.transfer', compact('warehouse', 'toBranches', 'products', 'recentTransfers'));
    }

    public function store(Request $request, Vendor $branch)
    {
        $warehouse = $branch;

        $request->validate([
            'to_vendor_id'        => 'required|integer',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|integer',
            'items.*.quantity'    => 'required|integer|min:1',
            'note'                => 'nullable|string|max:500',
        ]);

        $toVendor = Vendor::where('is_warehouse', false)->findOrFail($request->to_vendor_id);

        $items = collect($request->items)->map(fn($item) => [
            'product'  => Product::withoutGlobalScopes()->where('vendor_id', $warehouse->id)->find($item['product_id']),
            'quantity' => (int) $item['quantity'],
        ]);

        foreach ($items as $item) {
            if (!$item['product']) {
                return back()->with('error', 'One of the selected products no longer exists in the Warehouse.')->withInput();
            }
            if ($item['quantity'] > $item['product']->stock_qty) {
                return back()->with('error', "Cannot transfer {$item['quantity']} — Warehouse only has {$item['product']->stock_qty} of \"{$item['product']->name}\" in stock.")->withInput();
            }
        }

        DB::transaction(function () use ($warehouse, $toVendor, $items, $request) {
            $note = trim('Transferred' . ($request->note ? ' — ' . $request->note : ''));

            foreach ($items as $item) {
                $fromProduct = $item['product'];
                $quantity    = $item['quantity'];

                // The Warehouse's own row is itself a clone of the branch product that was purchased
                // (source_product_id points back to it) — resolve to that original "root" first.
                $root = $fromProduct->source_product_id
                    ? (Product::withoutGlobalScopes()->find($fromProduct->source_product_id) ?? $fromProduct)
                    : $fromProduct;

                if ($toVendor->id === $root->vendor_id) {
                    // Transferring back to the very branch that originated this product —
                    // top up its own row directly instead of creating a duplicate.
                    $toProduct = $root;
                } else {
                    // Match by source_product_id (not name) so a product is reliably
                    // recognized as "the same one" across transfers even if renamed later.
                    $toProduct = Product::withoutGlobalScopes()
                        ->where('vendor_id', $toVendor->id)
                        ->where('source_product_id', $root->id)
                        ->first();

                    if (!$toProduct) {
                        $categoryId = null;
                        if ($root->category_id) {
                            $categoryId = Category::withoutGlobalScopes()
                                ->where('vendor_id', $toVendor->id)
                                ->where('name', $root->category?->name)
                                ->value('id');

                            if (!$categoryId && $root->category) {
                                $categoryId = Category::create([
                                    'vendor_id' => $toVendor->id,
                                    'name'      => $root->category->name,
                                ])->id;
                            }
                        }

                        $toProduct = Product::create([
                            'vendor_id'          => $toVendor->id,
                            'category_id'        => $categoryId,
                            'source_product_id'  => $root->id,
                            'name'               => $root->name,
                            'sku'                => 'SKU-' . strtoupper(Str::random(8)),
                            'description'        => $root->description,
                            'image'              => $root->image,
                            'price'              => $root->price,
                            'cost_price'         => $root->cost_price,
                            'stock_qty'          => 0,
                            'unit'               => $root->unit,
                            'status'             => 'active',
                        ]);
                    }
                }

                Product::withoutGlobalScopes()->where('id', $fromProduct->id)->decrement('stock_qty', $quantity);
                Product::withoutGlobalScopes()->where('id', $toProduct->id)->increment('stock_qty', $quantity);

                StockMovement::create([
                    'vendor_id'      => $warehouse->id,
                    'product_id'     => $fromProduct->id,
                    'type'           => 'out',
                    'quantity'       => $quantity,
                    'reference_type' => 'transfer',
                    'note'           => $note . ' to ' . $toVendor->name,
                ]);

                StockMovement::create([
                    'vendor_id'      => $toVendor->id,
                    'product_id'     => $toProduct->id,
                    'type'           => 'in',
                    'quantity'       => $quantity,
                    'reference_type' => 'transfer',
                    'note'           => $note . ' from ' . $warehouse->name,
                ]);
            }
        });

        $count = $items->count();

        return redirect()->route('admin.warehouse.transfer')
            ->with('success', "Transferred {$count} product" . ($count > 1 ? 's' : '') . " to {$toVendor->name}.");
    }
}
