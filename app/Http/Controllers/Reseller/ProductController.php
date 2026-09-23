<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Show all active products of Admin Website (Online Store) only.
     * Supplier products and other physical branch products are strictly excluded.
     */
    public function index(Request $request)
    {
        $website = Vendor::onlineStore();

        $query = Product::withoutGlobalScopes()
            ->whereNull('supplier_id')
            ->where('vendor_id', $website->id)
            ->where('status', 'active')
            ->where('stock_qty', '>', 0)
            ->with(['category', 'variants', 'vendor', 'activeResellerFlashSale']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', $request->search);
            });
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate(24)->withQueryString();

        // ── Active Flash Deals for Top Showcase Shelf (Strictly for Resellers & Admin Website) ──
        $activeFlashIds = FlashSale::active()->forReseller()->pluck('product_id')->unique();
        $sourceProductIds = Product::withoutGlobalScopes()
            ->whereNull('supplier_id')
            ->where('vendor_id', $website->id)
            ->whereIn('id', $activeFlashIds)
            ->whereNotNull('source_product_id')
            ->pluck('source_product_id');

        $allResellerFlashIds = $activeFlashIds->merge($sourceProductIds)->unique();

        $flashSaleProducts = Product::withoutGlobalScopes()
            ->whereNull('supplier_id')
            ->where('vendor_id', $website->id)
            ->where('status', 'active')
            ->where('stock_qty', '>', 0)
            ->where(function ($q) use ($allResellerFlashIds) {
                $q->whereIn('id', $allResellerFlashIds)
                  ->orWhereIn('source_product_id', $allResellerFlashIds);
            })
            ->with(['category', 'variants', 'vendor', 'activeResellerFlashSale'])
            ->take(12)
            ->get();

        // Categories strictly for Website store
        $categories = Category::withoutGlobalScopes()
            ->where('vendor_id', $website->id)
            ->orderBy('name')
            ->get();

        return view('reseller.products.index', compact('products', 'categories', 'flashSaleProducts', 'website'));
    }

    /**
     * Show detailed product view strictly for Admin Website products.
     */
    public function show($id)
    {
        $website = Vendor::onlineStore();

        $product = Product::withoutGlobalScopes()
            ->whereNull('supplier_id')
            ->where('vendor_id', $website->id)
            ->where('status', 'active')
            ->with(['category', 'variants', 'vendor', 'activeResellerFlashSale'])
            ->findOrFail($id);

        $related = Product::withoutGlobalScopes()
            ->whereNull('supplier_id')
            ->where('vendor_id', $website->id)
            ->where('status', 'active')
            ->where('stock_qty', '>', 0)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        return view('reseller.products.show', compact('product', 'related', 'website'));
    }
}
