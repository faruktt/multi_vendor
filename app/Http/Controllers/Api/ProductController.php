<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(
            Product::with(['category', 'variants'])->latest()->get()->map(fn($p) => $this->transform($p))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => ['required', Rule::exists('categories', 'id')->where('vendor_id', $request->user()->vendor_id)],
            'price'       => 'required|numeric|min:0',
            'cost_price'  => 'required|numeric|min:0',
            'stock_qty'   => 'nullable|integer|min:0',
            'unit'        => 'nullable|string|max:50',
            'barcode'     => 'nullable|string|unique:products,barcode',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'variants'    => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'uploads');
        }

        $variants = [];
        if ($request->has('variants') && $request->variants) {
            $variants = json_decode($request->variants, true) ?? [];
        }

        $hasVariants = !empty($variants);

        $product = Product::create([
            'vendor_id'   => $request->user()->vendor_id,
            'category_id' => $validated['category_id'],
            'name'        => $validated['name'],
            'sku'         => 'SKU-' . strtoupper(Str::random(8)),
            'barcode'     => $validated['barcode'] ?? null,
            'image'       => $imagePath,
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'],
            'cost_price'  => $validated['cost_price'],
            'stock_qty'   => $hasVariants ? collect($variants)->sum('stock_qty') : ($validated['stock_qty'] ?? 0),
            'unit'        => $validated['unit'] ?? 'pcs',
        ]);

        foreach ($variants as $v) {
            ProductVariant::create([
                'product_id'   => $product->id,
                'variant_name' => $v['variant_name'],
                'attributes'   => $v['attributes'] ?? null,
                'price'        => $v['price'] ?? null,
                'cost_price'   => $v['cost_price'] ?? null,
                'stock_qty'    => $v['stock_qty'] ?? 0,
                'sku'          => 'VAR-' . strtoupper(Str::random(8)),
            ]);
        }

        return response()->json($this->transform($product->load(['category', 'variants'])), 201);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => ['required', Rule::exists('categories', 'id')->where('vendor_id', $request->user()->vendor_id)],
            'price'       => 'required|numeric|min:0',
            'cost_price'  => 'required|numeric|min:0',
            'stock_qty'   => 'nullable|integer|min:0',
            'unit'        => 'nullable|string|max:50',
            'barcode'     => 'nullable|string|unique:products,barcode,' . $product->id,
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'variants'    => 'nullable|string',
        ]);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('uploads')->delete($product->image);
            $imagePath = $request->file('image')->store('products', 'uploads');
        }

        $variants = [];
        if ($request->has('variants') && $request->variants) {
            $variants = json_decode($request->variants, true) ?? [];
        }

        $hasVariants = !empty($variants);

        $product->update([
            'category_id' => $validated['category_id'],
            'name'        => $validated['name'],
            'price'       => $validated['price'],
            'cost_price'  => $validated['cost_price'],
            'stock_qty'   => $hasVariants ? collect($variants)->sum('stock_qty') : ($validated['stock_qty'] ?? $product->stock_qty),
            'unit'        => $validated['unit'] ?? $product->unit,
            'barcode'     => $validated['barcode'] ?? $product->barcode,
            'image'       => $imagePath,
            'description' => $validated['description'] ?? $product->description,
        ]);

        if ($hasVariants) {
            $incomingIds = collect($variants)->pluck('id')->filter();
            $product->variants()->whereNotIn('id', $incomingIds)->delete();

            foreach ($variants as $v) {
                $data = [
                    'product_id'   => $product->id,
                    'variant_name' => $v['variant_name'],
                    'attributes'   => $v['attributes'] ?? null,
                    'price'        => $v['price'] ?? null,
                    'cost_price'   => $v['cost_price'] ?? null,
                    'stock_qty'    => $v['stock_qty'] ?? 0,
                ];
                if (!empty($v['id'])) {
                    ProductVariant::where('id', $v['id'])->update($data);
                } else {
                    ProductVariant::create(array_merge($data, ['sku' => 'VAR-' . strtoupper(Str::random(8))]));
                }
            }
            $product->update(['stock_qty' => collect($variants)->sum('stock_qty')]);
        }

        return response()->json($this->transform($product->load(['category', 'variants'])));
    }

    public function generateBarcode(Product $product)
    {
        if (!$product->barcode) {
            do {
                $code = sprintf('%04d%08d', $product->vendor_id, $product->id)
                    . strtoupper(Str::random(2));
            } while (Product::withoutGlobalScopes()->where('barcode', $code)->where('id', '!=', $product->id)->exists());

            $product->update(['barcode' => $code]);
        }

        return response()->json([
            'id'      => $product->id,
            'barcode' => $product->barcode,
        ]);
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('uploads')->delete($product->image);
        $product->delete();
        return response()->json(['message' => 'Deleted']);
    }

    private function transform($product)
    {
        $data = $product->toArray();
        if (!empty($data['image'])) {
            $data['image'] = Storage::disk('uploads')->url($data['image']);
        }
        return $data;
    }
}
