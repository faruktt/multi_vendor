<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /** List all products owned by this supplier */
    public function index(Request $request)
    {
        $supplier = auth('supplier')->user();

        $query = Product::withoutGlobalScopes()
            ->where('supplier_id', $supplier->id)
            ->with(['category', 'variants.color', 'variants.size'])
            ->withSum('saleItems as sold_qty', 'quantity')
            ->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%")
                  ->orWhere('barcode', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('stock')) {
            if ($request->stock === 'low') {
                $query->where('stock_qty', '>', 0)->where('stock_qty', '<=', 5);
            } elseif ($request->stock === 'out') {
                $query->where('stock_qty', '<=', 0);
            }
        }

        $products = $query->paginate(20)->withQueryString();

        $counts = [
            'all'      => Product::withoutGlobalScopes()->where('supplier_id', $supplier->id)->count(),
            'pending'  => Product::withoutGlobalScopes()->where('supplier_id', $supplier->id)->where('approval_status', 'pending')->count(),
            'approved' => Product::withoutGlobalScopes()->where('supplier_id', $supplier->id)->where('approval_status', 'approved')->count(),
            'rejected' => Product::withoutGlobalScopes()->where('supplier_id', $supplier->id)->where('approval_status', 'rejected')->count(),
        ];

        return view('supplier.products.index', compact('products', 'supplier', 'counts'));
    }

    /** Form to create a new product */
    public function create()
    {
        $supplier = auth('supplier')->user();
        $onlineStore = Vendor::onlineStore();

        $categories = Category::withoutGlobalScopes()
            ->where('vendor_id', $onlineStore->id)
            ->orderBy('name')
            ->get();

        $colors = Color::orderBy('sort_order')->orderBy('name')->get();
        $sizes  = Size::orderBy('sort_order')->orderBy('name')->get();

        return view('supplier.products.form', compact('categories', 'colors', 'sizes', 'supplier'));
    }

    /** Save a new product uploaded by supplier */
    public function store(Request $request)
    {
        $supplier = auth('supplier')->user();
        $onlineStore = Vendor::onlineStore();

        $request->validate([
            'name'               => 'required|string|max:255',
            'category_id'        => 'required|exists:categories,id',
            'price'              => 'required|numeric|min:0',
            'cost_price'         => 'nullable|numeric|min:0',
            'reseller_price'     => 'nullable|numeric|min:0',
            'stock_qty'          => 'nullable|integer|min:0',
            'unit'               => 'nullable|string|max:50',
            'barcode'            => 'nullable|string|max:100',
            'short_description'  => 'nullable|string|max:500',
            'description'        => 'nullable|string',
            'facebook_video_url' => 'nullable|string|max:1000',
            'images'             => 'nullable|array|max:10',
            'images.*'           => 'image|max:3072',
            'status'             => 'nullable|in:active,inactive',
        ]);

        $imageFilenames = $this->uploadImages($request->file('images', []));
        $variants = json_decode($request->variants ?? '[]', true) ?? [];
        $hasVariants = !empty($variants);

        $product = DB::transaction(function () use ($request, $supplier, $onlineStore, $imageFilenames, $variants, $hasVariants) {
            $sku = 'SP-' . strtoupper(Str::random(8));
            $barcode = $request->barcode ?: sprintf('%04d%08d', $onlineStore->id, rand(10000000, 99999999));

            $product = Product::create([
                'vendor_id'             => $onlineStore->id,
                'supplier_id'           => $supplier->id,
                'category_id'           => $request->category_id,
                'name'                  => $request->name,
                'sku'                   => $sku,
                'barcode'               => $barcode,
                'images'                => $imageFilenames,
                'image'                 => $imageFilenames[0] ?? null,
                'description'           => $request->description,
                'short_description'     => $request->short_description,
                'facebook_video_url'    => $request->facebook_video_url ? trim($request->facebook_video_url) : null,
                'price'                 => $request->price,
                'cost_price'            => $request->cost_price ?: 0,
                'reseller_price'        => $request->reseller_price ?: null,
                'stock_qty'             => $hasVariants ? collect($variants)->sum(fn($v) => (int)($v['stock_qty'] ?? 0)) : ($request->stock_qty ?? 0),
                'unit'                  => $request->unit ?? 'pcs',
                'status'                => 'inactive',
                'approval_status'       => 'pending',
                'admin_commission_rate' => null,
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
                    'variant_name' => trim(implode(' / ', array_filter([$colorLabel, $sizeLabel]))) ?: ($v['variant_name'] ?? 'Default'),
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
                    'vendor_id'      => $onlineStore->id,
                    'product_id'     => $product->id,
                    'type'           => 'in',
                    'quantity'       => $product->stock_qty,
                    'reference_type' => 'adjustment',
                    'note'           => 'Supplier initial stock entry: ' . $supplier->display_name,
                ]);
            }

            return $product;
        });

        $msg = 'প্রোডাক্টটি সফলভাবে জমা দেওয়া হয়েছে! অ্যাডমিন কমিশন নির্ধারণ ও অ্যাপ্রুভ করার পর এটি ওয়েবসাইটে দৃশ্যমান হবে।';
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'redirect_url' => route('supplier.products.index'),
                'message'      => $msg,
            ]);
        }

        return redirect()->route('supplier.products.index')->with('success', $msg);
    }

    /** Form to edit product */
    public function edit(Product $product)
    {
        $supplier = auth('supplier')->user();
        abort_unless($product->supplier_id === $supplier->id, 403, 'Unauthorized access.');

        $onlineStore = Vendor::onlineStore();
        $categories = Category::withoutGlobalScopes()
            ->where('vendor_id', $onlineStore->id)
            ->orderBy('name')
            ->get();

        $colors = Color::orderBy('sort_order')->orderBy('name')->get();
        $sizes  = Size::orderBy('sort_order')->orderBy('name')->get();

        $product->load(['variants.color', 'variants.size']);

        return view('supplier.products.form', compact('product', 'categories', 'colors', 'sizes', 'supplier'));
    }

    /** Update product */
    public function update(Request $request, Product $product)
    {
        $supplier = auth('supplier')->user();
        abort_unless($product->supplier_id === $supplier->id, 403, 'Unauthorized access.');

        $request->validate([
            'name'               => 'required|string|max:255',
            'category_id'        => 'required|exists:categories,id',
            'price'              => 'required|numeric|min:0',
            'cost_price'         => 'nullable|numeric|min:0',
            'reseller_price'     => 'nullable|numeric|min:0',
            'stock_qty'          => 'nullable|integer|min:0',
            'unit'               => 'nullable|string|max:50',
            'barcode'            => 'nullable|string|max:100',
            'short_description'  => 'nullable|string|max:500',
            'description'        => 'nullable|string',
            'facebook_video_url' => 'nullable|string|max:1000',
            'images'             => 'nullable|array|max:10',
            'images.*'           => 'image|max:3072',
            'status'             => 'nullable|in:active,inactive',
        ]);

        // Process existing images & removals
        $currentImages = $product->images_list ?: [];
        $toRemove = $request->input('remove_images', []);
        if (!empty($toRemove)) {
            $this->deleteImages($toRemove);
            $currentImages = array_values(array_diff($currentImages, $toRemove));
        }

        // Add new uploaded images
        $newFilenames = $this->uploadImages($request->file('images', []));
        $allImages = array_values(array_merge($currentImages, $newFilenames));

        $variants = json_decode($request->variants ?? '[]', true) ?? [];
        $hasVariants = !empty($variants);

        DB::transaction(function () use ($request, $product, $allImages, $variants, $hasVariants) {
            $product->update([
                'category_id'        => $request->category_id,
                'name'               => $request->name,
                'barcode'            => $request->barcode ?: $product->barcode,
                'images'             => $allImages,
                'image'              => $allImages[0] ?? null,
                'description'        => $request->description,
                'short_description'  => $request->short_description,
                'facebook_video_url' => $request->facebook_video_url ? trim($request->facebook_video_url) : null,
                'price'              => $request->price,
                'cost_price'         => $request->cost_price ?: 0,
                'reseller_price'     => $request->reseller_price ?: null,
                'stock_qty'          => $hasVariants ? collect($variants)->sum(fn($v) => (int)($v['stock_qty'] ?? 0)) : ($request->stock_qty ?? 0),
                'unit'               => $request->unit ?? 'pcs',
                'status'             => $request->status ?? $product->status,
            ]);

            // Sync variants: delete old variants and create new
            $product->variants()->delete();

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
                    'variant_name' => trim(implode(' / ', array_filter([$colorLabel, $sizeLabel]))) ?: ($v['variant_name'] ?? 'Default'),
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
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('supplier.products.index'),
                'message' => 'Product updated successfully!',
            ]);
        }

        return redirect()->route('supplier.products.index')
            ->with('success', 'Product "' . $product->name . '" updated successfully!');
    }

    /** Fast toggle active/inactive status */
    public function toggleStatus(Product $product)
    {
        $supplier = auth('supplier')->user();
        abort_unless($product->supplier_id === $supplier->id, 403);

        $newStatus = $product->status === 'active' ? 'inactive' : 'active';
        $product->update(['status' => $newStatus]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'status' => $newStatus]);
        }

        return back()->with('success', 'Product status changed to ' . ucfirst($newStatus) . '.');
    }

    /** Delete or deactivate product */
    public function destroy(Product $product)
    {
        $supplier = auth('supplier')->user();
        abort_unless($product->supplier_id === $supplier->id, 403);

        // If product has been ordered, mark as inactive to prevent breaking order history
        if ($product->saleItems()->exists()) {
            $product->update(['status' => 'inactive']);
            return back()->with('info', 'Product has existing sales history. It has been marked as inactive rather than deleted.');
        }

        $this->deleteImages($product->images_list ?? []);
        $product->variants()->delete();
        $product->delete();

        return back()->with('success', 'Product deleted successfully.');
    }

    /* ── Helper methods for image handling ── */
    private function uploadImages(array $files): array
    {
        $names = [];
        $uploadDir = public_path('uploads/products');
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;
            $filename = uniqid('img_', true) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $names[] = $filename;
        }
        return $names;
    }

    private function deleteImages(array $filenames): void
    {
        foreach ($filenames as $fn) {
            if (!$fn) continue;
            $path = str_contains($fn, '/')
                ? public_path('uploads/' . $fn)
                : public_path('uploads/products/' . $fn);
            if (file_exists($path)) @unlink($path);
        }
    }
}
