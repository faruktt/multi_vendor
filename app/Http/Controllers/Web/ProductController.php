<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request, Vendor $branch)
    {
        $query = $branch->products()->with(['category', 'variants'])->latest();

        if ($request->search)      $query->where('name', 'like', '%' . $request->search . '%');
        if ($request->category_id) $query->where('category_id', $request->category_id);
        if ($request->stock === 'low')  $query->where('stock_qty', '<=', 5)->where('stock_qty', '>', 0);
        if ($request->stock === 'out')  $query->where('stock_qty', '<=', 0);

        $products   = $query->paginate(20)->withQueryString();
        $categories = $branch->categories()->orderBy('name')->get();

        $products->getCollection()->transform(function ($p) {
            if ($p->image) $p->image_url = Storage::disk('uploads')->url($p->image);
            return $p;
        });

        return view('products.index', compact('branch', 'products', 'categories'));
    }

    public function create(Vendor $branch)
    {
        $allCategoryNames = Category::withoutGlobalScopes()->distinct()->pluck('name');
        foreach ($allCategoryNames as $catName) {
            Category::withoutGlobalScopes()->firstOrCreate([
                'vendor_id' => $branch->id,
                'name'      => $catName,
            ]);
        }
        $categories = $branch->categories()->orderBy('name')->get();
        $colors     = Color::orderBy('sort_order')->orderBy('name')->get();
        $sizes      = Size::orderBy('sort_order')->orderBy('name')->get();
        return view('products.form', compact('branch', 'categories', 'colors', 'sizes'));
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
            $path = str_contains($fn, '/') ? public_path('uploads/' . $fn) : public_path('uploads/products/' . $fn);
            if (file_exists($path)) @unlink($path);
        }
    }

    public function store(Request $request, Vendor $branch)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => ['required', Rule::exists('categories', 'id')->where('vendor_id', $branch->id)],
            'price'             => 'required|numeric|min:0',
            'old_price'         => 'nullable|numeric|min:0',
            'cost_price'        => 'required|numeric|min:0',
            'reseller_price'    => 'nullable|numeric|min:0',
            'stock_qty'         => 'nullable|integer|min:0',
            'unit'              => 'nullable|string|max:50',
            'barcode'           => 'nullable|string|unique:products,barcode',
            'description'        => 'nullable|string',
            'short_description'  => 'nullable|string|max:500',
            'facebook_video_url' => 'nullable|string|max:1000',
            'images'             => 'nullable|array|max:10',
            'images.*'           => 'image|max:2048',
        ]);

        $imageFilenames = $this->uploadImages($request->file('images', []));

        $variants   = json_decode($request->variants ?? '[]', true) ?? [];
        $hasVariants = !empty($variants);

        $product = $branch->products()->create([
            'category_id'        => $request->category_id,
            'name'               => $request->name,
            'sku'                => 'SKU-' . strtoupper(Str::random(8)),
            'barcode'            => $request->barcode,
            'images'             => $imageFilenames,
            'image'              => $imageFilenames[0] ?? null, // legacy compat
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

        return redirect()->route('branch.products.index', $branch)->with('success', 'Product added.');
    }

    public function edit(Vendor $branch, Product $product)
    {
        $allCategoryNames = Category::withoutGlobalScopes()->distinct()->pluck('name');
        foreach ($allCategoryNames as $catName) {
            Category::withoutGlobalScopes()->firstOrCreate([
                'vendor_id' => $branch->id,
                'name'      => $catName,
            ]);
        }
        $categories = $branch->categories()->orderBy('name')->get();
        $colors     = Color::orderBy('sort_order')->orderBy('name')->get();
        $sizes      = Size::orderBy('sort_order')->orderBy('name')->get();
        $product->load('variants');
        return view('products.form', compact('branch', 'categories', 'product', 'colors', 'sizes'));
    }

    public function update(Request $request, Vendor $branch, Product $product)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => ['required', Rule::exists('categories', 'id')->where('vendor_id', $branch->id)],
            'price'             => 'required|numeric|min:0',
            'old_price'         => 'nullable|numeric|min:0',
            'cost_price'        => 'required|numeric|min:0',
            'reseller_price'    => 'nullable|numeric|min:0',
            'stock_qty'         => 'nullable|integer|min:0',
            'unit'              => 'nullable|string|max:50',
            'barcode'           => 'nullable|string|unique:products,barcode,' . $product->id,
            'description'        => 'nullable|string',
            'short_description'  => 'nullable|string|max:500',
            'facebook_video_url' => 'nullable|string|max:1000',
            'images'             => 'nullable|array|max:10',
            'images.*'           => 'image|max:2048',
            'remove_images'      => 'nullable|array',
            'remove_images.*'    => 'string',
        ]);

        // 1. Start with existing images
        $currentImages = $product->images_list;

        // 2. Remove deleted ones
        $toRemove = $request->input('remove_images', []);
        if (!empty($toRemove)) {
            $this->deleteImages($toRemove);
            $currentImages = array_values(array_diff($currentImages, $toRemove));
        }

        // 3. Add new uploads
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
            'image'              => $allImages[0] ?? null, // legacy compat
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
                'vendor_id'      => $branch->id,
                'product_id'     => $product->id,
                'type'           => $stockDelta > 0 ? 'in' : 'out',
                'quantity'       => abs($stockDelta),
                'reference_type' => 'adjustment',
                'note'           => 'Manual stock adjustment',
            ]);
        }

        return redirect()->route('branch.products.index', $branch)->with('success', 'Product updated.');
    }

    public function showBarcode(Vendor $branch, Product $product)
    {
        // Auto-generate barcode value if missing
        if (!$product->barcode) {
            $code = sprintf('%04d%08d', $branch->id, $product->id);
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

    public function bulkBarcode(Request $request, Vendor $branch)
    {
        $ids = array_filter(explode(',', $request->input('ids', '')));
        if (empty($ids)) {
            return back()->with('error', 'No products selected.');
        }

        $products = $branch->products()->whereIn('id', $ids)->with('category')->get();
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();

        $items = $products->map(function ($p) use ($branch, $generator) {
            if (!$p->barcode) {
                $code = sprintf('%04d%08d', $branch->id, $p->id);
                $p->update(['barcode' => $code]);
            }
            $p->barcode_svg = $generator->getBarcode(
                $p->barcode,
                \Picqer\Barcode\BarcodeGeneratorSVG::TYPE_CODE_128,
                2, 80
            );
            return $p;
        });

        return view('products.barcode-print', compact('branch', 'items'));
    }

    public function generateBarcode(Vendor $branch, Product $product)
    {
        if (!$product->barcode) {
            do {
                $code = sprintf('%04d%08d', $branch->id, $product->id) . strtoupper(Str::random(2));
            } while (Product::withoutGlobalScopes()->where('barcode', $code)->where('id', '!=', $product->id)->exists());
            $product->update(['barcode' => $code]);
        }
        return response()->json(['barcode' => $product->barcode]);
    }
}
