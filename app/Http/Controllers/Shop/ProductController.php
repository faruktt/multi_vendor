<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, ?string $categorySlug = null)
    {
        $branch = Vendor::onlineStore();
        abort_unless($branch->status === 'active', 404);

        $category = $categorySlug
            ? Category::withoutGlobalScopes()->where('vendor_id', $branch->id)->where('slug', $categorySlug)->firstOrFail()
            : null;

        $categories = $this->sidebarCategories($branch->id);

        $query = Product::withoutGlobalScopes()
            ->with(['category', 'supplier'])
            ->where('vendor_id', $branch->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('supplier_id')->orWhere('approval_status', 'approved');
            });

        if ($category) {
            $query->where('category_id', $category->id);
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->float('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->float('max_price'));
        }

        if (!$request->filled('sort') || $request->get('sort') === 'default') {
            $products = \App\Services\ProductFeedService::getBalancedPaginatedFeed(
                branch: $branch,
                categoryId: $category?->id,
                perPage: 20,
                page: (int) $request->get('page', 1),
                search: $request->q,
                minPrice: $request->filled('min_price') ? $request->float('min_price') : null,
                maxPrice: $request->filled('max_price') ? $request->float('max_price') : null
            );
        } else {
            match ($request->get('sort')) {
                'price_asc'  => $query->orderBy('price', 'asc'),
                'price_desc' => $query->orderBy('price', 'desc'),
                'name'       => $query->orderBy('name', 'asc'),
                default      => $query->latest(),
            };
            $products = $query->paginate(20)->withQueryString();
        }
        $activeCategory = $category;

        return view('shop.products.index', compact('branch', 'categories', 'products', 'activeCategory'));
    }

    public function show(string $productSlug)
    {
        $branch = Vendor::onlineStore();
        abort_unless($branch->status === 'active', 404);

        $product = Product::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('status', 'active')
            ->where('slug', $productSlug)
            ->where(function ($q) {
                $q->whereNull('supplier_id')->orWhere('approval_status', 'approved');
            })
            ->with(['category', 'variants.color', 'variants.size', 'supplier'])
            ->firstOrFail();

        $related = Product::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('status', 'active')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where(function ($q) {
                $q->whereNull('supplier_id')->orWhere('approval_status', 'approved');
            })
            ->inRandomOrder()
            ->limit(12)
            ->get();

        $categories = $this->sidebarCategories($branch->id);

        return view('shop.products.show', compact('branch', 'product', 'related', 'categories'));
    }

    public function quickView(Request $request, string $productSlug)
    {
        $branch = Vendor::onlineStore();
        abort_unless($branch->status === 'active', 404);

        $product = Product::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('status', 'active')
            ->where('slug', $productSlug)
            ->where(function ($q) {
                $q->whereNull('supplier_id')->orWhere('approval_status', 'approved');
            })
            ->with(['category', 'variants.color', 'variants.size', 'supplier'])
            ->firstOrFail();

        $currency = '৳';
        return view('shop.partials.quick-view-drawer', compact('branch', 'product', 'currency'));
    }

    public function supplierStore(Request $request, \App\Models\Supplier $supplier)
    {
        $branch = Vendor::onlineStore();
        abort_unless($branch->status === 'active', 404);
        abort_unless($supplier->isActive(), 404);

        $categories = $this->sidebarCategories($branch->id);

        $query = Product::withoutGlobalScopes()
            ->with('category')
            ->where('vendor_id', $branch->id)
            ->where('supplier_id', $supplier->id)
            ->where('status', 'active')
            ->where('approval_status', 'approved');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        match ($request->get('sort')) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name'       => $query->orderBy('name', 'asc'),
            default      => $query->latest(),
        };

        $products = $query->paginate(20)->withQueryString();

        return view('shop.suppliers.show', compact('branch', 'supplier', 'categories', 'products'));
    }

    private function sidebarCategories(int $vendorId)
    {
        return Category::withoutGlobalScopes()
            ->where('vendor_id', $vendorId)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->withoutGlobalScopes()->orderBy('name')])
            ->orderBy('name')
            ->get();
    }
}
