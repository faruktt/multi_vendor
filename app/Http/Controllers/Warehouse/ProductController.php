<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SaleItem;
use App\Models\Size;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /** Warehouse has no category management UI — silently file its own products under one bucket category. */
    private function warehouseCategoryId(Vendor $warehouse): int
    {
        return Category::withoutGlobalScopes()->firstOrCreate(
            ['vendor_id' => $warehouse->id, 'name' => 'General']
        )->id;
    }

    /** Same category, found-or-created by name in the destination branch — mirrors TransferController. */
    private function resolveBranchCategory(Vendor $toBranch, ?Category $sourceCategory): ?int
    {
        if (!$sourceCategory) return null;

        return Category::withoutGlobalScopes()
            ->where('vendor_id', $toBranch->id)
            ->where('name', $sourceCategory->name)
            ->value('id')
            ?? Category::create(['vendor_id' => $toBranch->id, 'name' => $sourceCategory->name])->id;
    }

    /** Active, non-warehouse branches — the only valid distribution/purchase targets. */
    private function distributableBranches()
    {
        return Vendor::where('status', 'active')->where('is_warehouse', false)->orderBy('name')->get();
    }

    /**
     * One row per product family (its root — source_product_id is null), regardless of
     * whether it originated at the Warehouse itself or was entered at a branch before
     * this became the only entry point.
     */
    public function index(Request $request, Vendor $branch)
    {
        $query = Product::withoutGlobalScopes()
            ->whereNull('source_product_id')
            ->with(['category', 'variants', 'vendor'])
            ->latest();

        if ($request->search) $query->where('name', 'like', '%' . $request->search . '%');
        if ($request->stock === 'low') $query->where('stock_qty', '<=', 5)->where('stock_qty', '>', 0);
        if ($request->stock === 'out') $query->where('stock_qty', '<=', 0);

        $products = $query->paginate(20)->withQueryString();

        $products->getCollection()->transform(function ($p) use ($branch) {
            if ($p->image) $p->image_url = Storage::disk('uploads')->url($p->image);

            // Every downstream clone (Warehouse's own stock row, and any branch it was
            // transferred to) shares this root row's id as source_product_id directly.
            $downstream = Product::withoutGlobalScopes()->where('source_product_id', $p->id)->with('vendor')->get(['id', 'vendor_id']);
            $downstreamIds = $downstream->pluck('id');

            // Every location this product is actually available at — its origin plus
            // every branch it's been distributed/transferred to (excluding the Warehouse's
            // own internal stock-pool clone, which isn't a "branch" from a catalog standpoint).
            $p->branch_names = collect([$p->vendor?->name])
                ->merge($downstream->reject(fn($d) => $d->vendor_id === $branch->id)->pluck('vendor.name'))
                ->filter()->unique()->values();

            // If the root itself already lives at the Warehouse, that IS the stock pool —
            // no separate clone to look up. Otherwise (legacy branch-originated product),
            // find the Warehouse's own clone of it, if one has been purchased yet.
            $warehouseStockId = $p->vendor_id === $branch->id
                ? $p->id
                : Product::withoutGlobalScopes()
                    ->where('vendor_id', $branch->id)
                    ->where('source_product_id', $p->id)
                    ->value('id');

            $p->transferred_qty = $warehouseStockId
                ? StockMovement::withoutGlobalScopes()
                    ->where('product_id', $warehouseStockId)
                    ->where('type', 'out')
                    ->where('reference_type', 'transfer')
                    ->sum('quantity')
                : 0;

            $p->sold_qty = SaleItem::whereIn('product_id', $downstreamIds->push($p->id))->sum('quantity');

            return $p;
        });

        return view('warehouse.products.index', compact('branch', 'products'));
    }

    public function create(Vendor $branch)
    {
        $this->warehouseCategoryId($branch);
        $allCategoryNames = Category::withoutGlobalScopes()->distinct()->pluck('name');
        foreach ($allCategoryNames as $catName) {
            Category::withoutGlobalScopes()->firstOrCreate([
                'vendor_id' => $branch->id,
                'name'      => $catName,
            ]);
        }
        $categories = $branch->categories()->orderBy('name')->get();
        $toBranches = $this->distributableBranches();
        $colors     = Color::orderBy('sort_order')->orderBy('name')->get();
        $sizes      = Size::orderBy('sort_order')->orderBy('name')->get();

        return view('warehouse.products.form', compact('branch', 'categories', 'toBranches', 'colors', 'sizes'));
    }

    /** Save uploaded images to public/uploads/products/ and return filename array. */
    private function uploadImages(array $files): array
    {
        $names = [];
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;
            $filename = uniqid('img_', true) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $names[] = $filename;
        }
        return $names;
    }

    /** Delete image files from public/uploads/products/. */
    private function deleteImages(array $filenames): void
    {
        foreach ($filenames as $fn) {
            if (!$fn) continue;
            // Handle both plain filename and legacy 'products/filename.ext' paths
            if (str_contains($fn, '/')) {
                $path = public_path('uploads/' . $fn);
            } else {
                $path = public_path('uploads/products/' . $fn);
            }
            if (file_exists($path)) @unlink($path);
        }
    }

    public function store(Request $request, Vendor $branch)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => ['required', Rule::exists('categories', 'id')->where('vendor_id', $branch->id)],
            'price'         => 'required|numeric|min:0',
            'old_price'     => 'nullable|numeric|min:0',
            'cost_price'    => 'required|numeric|min:0',
            'reseller_price'=> 'nullable|numeric|min:0',
            'stock_qty'     => 'nullable|integer|min:0',
            'unit'          => 'nullable|string|max:50',
            'barcode'       => 'nullable|string|unique:products,barcode',
            'description'        => 'nullable|string',
            'short_description'  => 'nullable|string|max:500',
            'facebook_video_url' => 'nullable|string|max:1000',
            'images'             => 'nullable|array|max:10',
            'images.*'           => 'image|max:2048',
            'branch_ids'         => 'nullable|array',
            'branch_ids.*'       => 'integer|exists:vendors,id',
        ]);

        $imageFilenames = $this->uploadImages($request->file('images', []));

        $variants    = json_decode($request->variants ?? '[]', true) ?? [];
        $hasVariants = !empty($variants);

        $validBranchIds = $this->distributableBranches()->pluck('id');
        $branchIds      = collect($request->branch_ids ?? [])->intersect($validBranchIds);

        $product = DB::transaction(function () use ($request, $branch, $imageFilenames, $variants, $hasVariants, $branchIds) {
            $product = Product::create([
                'vendor_id'          => $branch->id,
                'category_id'        => $request->category_id,
                'name'               => $request->name,
                'sku'                => 'SKU-' . strtoupper(Str::random(8)),
                'barcode'            => $request->barcode,
                'images'             => $imageFilenames,
                'image'              => $imageFilenames[0] ?? null,
                'description'        => $request->description,
                'short_description'  => $request->short_description,
                'facebook_video_url' => $request->facebook_video_url ? trim($request->facebook_video_url) : null,
                'price'              => $request->price,
                'old_price'          => $request->filled('old_price') ? $request->old_price : null,
                'cost_price'         => $request->cost_price,
                'reseller_price'     => $request->reseller_price,
                'stock_qty'          => $hasVariants ? collect($variants)->sum(fn($v) => (int)($v['stock_qty'] ?? 0)) : ($request->stock_qty ?? 0),
                'unit'               => $request->unit ?? 'pcs',
                'status'             => 'active',
            ]);

            foreach ($variants as $v) {
                $colorLabel = $v['color_label'] ?? null;
                $sizeLabel  = $v['size_label']  ?? null;
                $price      = (!isset($v['price']) || $v['price'] === '') ? null : $v['price'];
                $costPrice  = (!isset($v['cost_price']) || $v['cost_price'] === '') ? null : $v['cost_price'];
                $stockQty   = (!isset($v['stock_qty']) || $v['stock_qty'] === '') ? 0 : (int)$v['stock_qty'];
                $colorId    = !empty($v['color_id']) ? $v['color_id'] : null;
                $sizeId     = !empty($v['size_id']) ? $v['size_id'] : null;

                ProductVariant::create([
                    'product_id'   => $product->id,
                    'variant_name' => trim(implode(' / ', array_filter([$colorLabel, $sizeLabel]))) ?: ($v['variant_name'] ?? ''),
                    'attributes'   => $v['attributes'] ?? null,
                    'color_id'     => $colorId,
                    'size_id'      => $sizeId,
                    'color_label'  => $colorLabel,
                    'size_label'   => $sizeLabel,
                    'price'        => $price,
                    'cost_price'   => $costPrice,
                    'stock_qty'    => $stockQty,
                    'sku'          => 'VAR-' . strtoupper(Str::random(8)),
                ]);
            }

            if ($product->stock_qty > 0) {
                StockMovement::create([
                    'vendor_id'      => $branch->id,
                    'product_id'     => $product->id,
                    'type'           => 'in',
                    'quantity'       => $product->stock_qty,
                    'reference_type' => 'adjustment',
                    'note'           => 'Opening stock',
                ]);
            }

            // Distribute — every selected branch gets a 0-stock clone right away so the
            // product shows up in its catalog; real stock still only arrives via Purchase → Transfer.
            $product->load('category');
            foreach ($branchIds as $toBranchId) {
                $toBranch = Vendor::find($toBranchId);
                if (!$toBranch) continue;

                Product::create([
                    'vendor_id'          => $toBranch->id,
                    'category_id'        => $this->resolveBranchCategory($toBranch, $product->category),
                    'source_product_id'  => $product->id,
                    'name'               => $product->name,
                    'sku'                => 'SKU-' . strtoupper(Str::random(8)),
                    'description'        => $product->description,
                    'images'             => $product->images,
                    'image'              => $product->image,
                    'price'              => $product->price,
                    'old_price'          => $product->old_price,
                    'cost_price'         => $product->cost_price,
                    'reseller_price'     => $product->reseller_price,
                    'stock_qty'          => 0,
                    'unit'               => $product->unit,
                    'status'             => 'active',
                ]);
            }

            return $product;
        });

        return redirect()->route('admin.warehouse.products.index', 'warehouse')->with('success', 'Product added — available at ' . ($branchIds->count() ?: 'no') . ' branch' . ($branchIds->count() === 1 ? '' : 'es') . '.');
    }

    public function edit(Vendor $branch, Product $product)
    {
        abort_unless(is_null($product->source_product_id), 404); // only roots are managed here — the index only lists roots

        // The Warehouse manages every product's catalog now, including ones that originated at
        // a branch before this became the only entry point — so categories come from whichever
        // vendor actually owns this product, not always the Warehouse's own list.
        $owner      = $product->vendor_id === $branch->id ? $branch : Vendor::find($product->vendor_id);
        $categories = Category::withoutGlobalScopes()->where('vendor_id', $owner->id)->orderBy('name')->get();
        $toBranches = $this->distributableBranches();
        $colors     = Color::orderBy('sort_order')->orderBy('name')->get();
        $sizes      = Size::orderBy('sort_order')->orderBy('name')->get();
        $product->load('variants');
        $product->setRelation('vendor', $owner);
        if ($product->image) $product->image_url = Storage::disk('uploads')->url($product->image);

        // Branches that already carry a clone of this product — shown as locked-checked
        // (removing distribution isn't supported here; that branch may already have stock/sales).
        $distributedBranchIds = Product::withoutGlobalScopes()
            ->where('source_product_id', $product->id)
            ->pluck('vendor_id');

        // Branches with an unrelated product that just happens to share this exact name —
        // pre-dates the distribute feature, so there's no real link. Purely informational:
        // don't auto-check it, just flag it so it's not distributed as a blind duplicate.
        $sameNameBranchIds = Product::withoutGlobalScopes()
            ->where('name', $product->name)
            ->where('id', '!=', $product->id)
            ->whereNotIn('vendor_id', $distributedBranchIds)
            ->pluck('vendor_id');

        return view('warehouse.products.form', compact('branch', 'categories', 'product', 'toBranches', 'distributedBranchIds', 'sameNameBranchIds', 'colors', 'sizes'));
    }

    public function update(Request $request, Vendor $branch, Product $product)
    {
        abort_unless(is_null($product->source_product_id), 404);

        $request->validate([
            'name'           => 'required|string|max:255',
            'category_id'    => ['required', Rule::exists('categories', 'id')->where('vendor_id', $product->vendor_id)],
            'price'          => 'required|numeric|min:0',
            'old_price'      => 'nullable|numeric|min:0',
            'cost_price'     => 'required|numeric|min:0',
            'reseller_price' => 'nullable|numeric|min:0',
            'stock_qty'      => 'nullable|integer|min:0',
            'unit'           => 'nullable|string|max:50',
            'barcode'        => 'nullable|string|unique:products,barcode,' . $product->id,
            'description'        => 'nullable|string',
            'short_description'  => 'nullable|string|max:500',
            'facebook_video_url' => 'nullable|string|max:1000',
            'images'             => 'nullable|array|max:10',
            'images.*'           => 'image|max:2048',
            'remove_images'      => 'nullable|array',
            'remove_images.*'    => 'string',
            'branch_ids'         => 'nullable|array',
            'branch_ids.*'       => 'integer|exists:vendors,id',
        ]);

        $currentImages = $product->images_list;
        $toRemove = $request->input('remove_images', []);
        if (!empty($toRemove)) {
            $this->deleteImages($toRemove);
            $currentImages = array_values(array_diff($currentImages, $toRemove));
        }
        $newImages = $this->uploadImages($request->file('images', []));
        $allImages = array_values(array_merge($currentImages, $newImages));

        $variants    = json_decode($request->variants ?? '[]', true) ?? [];
        $hasVariants = !empty($variants);

        $oldStockQty = $product->stock_qty;

        $product->update([
            'category_id'        => $request->category_id,
            'name'               => $request->name,
            'price'              => $request->price,
            'old_price'          => $request->filled('old_price') ? $request->old_price : null,
            'cost_price'         => $request->cost_price,
            'reseller_price'     => $request->reseller_price,
            'stock_qty'          => $hasVariants ? collect($variants)->sum(fn($v) => (int)($v['stock_qty'] ?? 0)) : ($request->stock_qty ?? $product->stock_qty),
            'unit'               => $request->unit ?? $product->unit,
            'barcode'            => $request->barcode ?? $product->barcode,
            'images'             => $allImages,
            'image'              => $allImages[0] ?? null,
            'description'        => $request->description ?? $product->description,
            'short_description'  => $request->short_description ?? $product->short_description,
            'facebook_video_url' => $request->facebook_video_url ? trim($request->facebook_video_url) : null,
        ]);

        if ($hasVariants) {
            $incomingIds = collect($variants)->pluck('id')->filter();
            $product->variants()->whereNotIn('id', $incomingIds)->delete();

            foreach ($variants as $v) {
                $colorLabel = $v['color_label'] ?? null;
                $sizeLabel  = $v['size_label']  ?? null;
                $price      = (!isset($v['price']) || $v['price'] === '') ? null : $v['price'];
                $costPrice  = (!isset($v['cost_price']) || $v['cost_price'] === '') ? null : $v['cost_price'];
                $stockQty   = (!isset($v['stock_qty']) || $v['stock_qty'] === '') ? 0 : (int)$v['stock_qty'];
                $colorId    = !empty($v['color_id']) ? $v['color_id'] : null;
                $sizeId     = !empty($v['size_id']) ? $v['size_id'] : null;

                $data = [
                    'product_id'   => $product->id,
                    'variant_name' => trim(implode(' / ', array_filter([$colorLabel, $sizeLabel]))) ?: ($v['variant_name'] ?? ''),
                    'attributes'   => $v['attributes'] ?? null,
                    'color_id'     => $colorId,
                    'size_id'      => $sizeId,
                    'color_label'  => $colorLabel,
                    'size_label'   => $sizeLabel,
                    'price'        => $price,
                    'cost_price'   => $costPrice,
                    'stock_qty'    => $stockQty,
                ];
                if (!empty($v['id'])) {
                    ProductVariant::where('id', $v['id'])->update($data);
                } else {
                    ProductVariant::create(array_merge($data, ['sku' => 'VAR-' . strtoupper(Str::random(8))]));
                }
            }
            $product->update(['stock_qty' => collect($variants)->sum(fn($v) => (int)($v['stock_qty'] ?? 0))]);
        }

        $stockDelta = $product->stock_qty - $oldStockQty;
        if ($stockDelta !== 0) {
            StockMovement::create([
                'vendor_id'      => $product->vendor_id,
                'product_id'     => $product->id,
                'type'           => $stockDelta > 0 ? 'in' : 'out',
                'quantity'       => abs($stockDelta),
                'reference_type' => 'adjustment',
                'note'           => 'Manual stock adjustment',
            ]);
        }

        // Newly-checked branches get a fresh 0-stock clone; unchecking an already-distributed
        // branch removes it — but only if it's still at 0 stock, so nothing with real inventory
        // disappears silently just from a checkbox change.
        $validBranchIds       = $this->distributableBranches()->pluck('id');
        $requestedBranchIds   = collect($request->branch_ids ?? [])->intersect($validBranchIds);
        $alreadyDistributedTo = Product::withoutGlobalScopes()->where('source_product_id', $product->id)->pluck('vendor_id');
        $newBranchIds         = $requestedBranchIds->diff($alreadyDistributedTo);
        $removedBranchIds     = $alreadyDistributedTo->diff($requestedBranchIds);

        if ($newBranchIds->isNotEmpty()) {
            $product->load('category');
            foreach ($newBranchIds as $toBranchId) {
                $toBranch = Vendor::find($toBranchId);
                if (!$toBranch) continue;

                Product::create([
                    'vendor_id'          => $toBranch->id,
                    'category_id'        => $this->resolveBranchCategory($toBranch, $product->category),
                    'source_product_id'  => $product->id,
                    'name'               => $product->name,
                    'sku'                => 'SKU-' . strtoupper(Str::random(8)),
                    'description'        => $product->description,
                    'short_description'  => $product->short_description,
                    'facebook_video_url' => $product->facebook_video_url,
                    'images'             => $product->images,
                    'image'              => $product->image,
                    'price'              => $product->price,
                    'old_price'          => $product->old_price,
                    'cost_price'         => $product->cost_price,
                    'reseller_price'     => $product->reseller_price,
                    'stock_qty'          => 0,
                    'unit'               => $product->unit,
                    'status'             => 'active',
                ]);
            }
        }

        // Sync details to existing downstream clones
        Product::withoutGlobalScopes()->where('source_product_id', $product->id)->update([
            'short_description'  => $product->short_description,
            'facebook_video_url' => $product->facebook_video_url,
            'description'        => $product->description,
            'old_price'          => $product->old_price,
        ]);

        $blockedRemovals = [];
        foreach ($removedBranchIds as $vendorId) {
            $clone = Product::withoutGlobalScopes()->where('source_product_id', $product->id)->where('vendor_id', $vendorId)->first();
            if (!$clone) continue;

            if ($clone->stock_qty > 0) {
                $blockedRemovals[] = $clone->vendor?->name ?? "vendor #{$vendorId}";
                continue;
            }

            if ($clone->image) Storage::disk('uploads')->delete($clone->image);
            $clone->delete();
        }

        $message = 'Product updated.';
        if (!empty($blockedRemovals)) {
            $message .= ' Could not remove from ' . implode(', ', $blockedRemovals) . ' — it still has stock there; transfer it out first.';
        }

        return redirect()->route('admin.warehouse.products.edit', ['warehouse', $product])->with($blockedRemovals ? 'error' : 'success', $message);
    }

    public function destroy(Vendor $branch, Product $product)
    {
        abort_unless(is_null($product->source_product_id), 404);

        if ($product->image) Storage::disk('uploads')->delete($product->image);
        $product->delete();

        return back()->with('success', 'Product deleted.');
    }

    public function showBarcode(Vendor $branch, Product $product)
    {
        abort_unless(is_null($product->source_product_id), 404);

        if (!$product->barcode) {
            $code = sprintf('%04d%08d', $product->vendor_id, $product->id);
            $product->update(['barcode' => $code]);
        }

        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $svg = $generator->getBarcode(
            $product->barcode,
            \Picqer\Barcode\BarcodeGeneratorSVG::TYPE_CODE_128,
            2, 80
        );

        return view('products.barcode-print', compact('branch', 'product', 'svg'));
    }

    public function generateBarcode(Vendor $branch, Product $product)
    {
        abort_unless(is_null($product->source_product_id), 404);

        if (!$product->barcode) {
            do {
                $code = sprintf('%04d%08d', $product->vendor_id, $product->id) . strtoupper(Str::random(2));
            } while (Product::withoutGlobalScopes()->where('barcode', $code)->where('id', '!=', $product->id)->exists());
            $product->update(['barcode' => $code]);
        }
        return response()->json(['barcode' => $product->barcode]);
    }

    /**
     * Full stock-movement history for a product across every location it exists at —
     * its own branch, the Warehouse's clone (if purchased at least once), and any other
     * branch it's been transferred to. Answers "when/how did this product's stock change".
     */
    public function report(Vendor $branch, Product $product)
    {
        $warehouseClone = Product::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('source_product_id', $product->id)
            ->first();

        $otherBranchClones = Product::withoutGlobalScopes()
            ->where('source_product_id', $product->id)
            ->where('vendor_id', '!=', $branch->id)
            ->with('vendor')
            ->get();

        $product->loadMissing('vendor');
        $warehouseClone?->loadMissing('vendor');

        $locations = collect([$product])
            ->when($warehouseClone, fn($c) => $c->push($warehouseClone))
            ->merge($otherBranchClones);

        $familyIds = $locations->pluck('id');

        $movements = StockMovement::withoutGlobalScopes()
            ->whereIn('product_id', $familyIds)
            ->with(['vendor', 'product'])
            ->orderByDesc('created_at')
            ->paginate(30);

        $sumOf = fn($type, $referenceType) => StockMovement::withoutGlobalScopes()
            ->whereIn('product_id', $familyIds)
            ->where('type', $type)
            ->where('reference_type', $referenceType)
            ->sum('quantity');

        $totals = [
            'purchased'    => $sumOf('in', 'purchase'),
            'transferred'  => $sumOf('out', 'transfer'),
            'sold'         => $sumOf('out', 'sale'),
            'current_total'=> $locations->sum('stock_qty'),
        ];

        return view('warehouse.products.report', compact(
            'branch', 'product', 'warehouseClone', 'otherBranchClones', 'locations', 'movements', 'totals'
        ));
    }
}
