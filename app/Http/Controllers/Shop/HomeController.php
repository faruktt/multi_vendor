<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\HomeContent;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Vendor;
use App\Services\ProductFeedService;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index()
    {
        $branch = Vendor::onlineStore();
        abort_unless($branch->status === 'active', 404);

        $categories = Category::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->withoutGlobalScopes()->orderBy('name')])
            ->orderBy('name')
            ->get();

        // Balanced random feed across various suppliers and admin products
        $bestSelling = ProductFeedService::getBalancedFeed($branch, limit: 8);
        $newArrivals = ProductFeedService::getBalancedFeed($branch, limit: 8);

        $homepageCategories = Category::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->whereNull('parent_id')
            ->where('show_on_homepage', true)
            ->orderBy('homepage_sort_order')
            ->orderBy('name')
            ->get();

        $categoryShelves = $homepageCategories->map(function ($category) use ($branch) {
            return (object) [
                'category' => $category,
                'products' => ProductFeedService::getBalancedFeed($branch, categoryId: $category->id, limit: 8),
            ];
        })->filter(fn($shelf) => $shelf->products->isNotEmpty())->values();

        $stats = [
            'categories' => $categories->count(),
            'products'   => Product::withoutGlobalScopes()->where('vendor_id', $branch->id)->where('status', 'active')->count(),
            'orders'     => Sale::withoutGlobalScopes()->where('vendor_id', $branch->id)->count(),
        ];

        $heroBanners = Banner::where('is_active', true)->where('position', 'hero')->orderBy('sort_order')->get()
            ->map(fn($b) => tap($b, fn($b) => $b->image_url = Storage::disk('uploads')->url($b->image)));

        $promo1Banner = Banner::where('is_active', true)->where('position', 'promo1')->orderBy('sort_order')->first();
        if ($promo1Banner) $promo1Banner->image_url = Storage::disk('uploads')->url($promo1Banner->image);

        $promo2Banners = Banner::where('is_active', true)->where('position', 'promo2')->orderBy('sort_order')->get()
            ->map(fn($b) => tap($b, fn($b) => $b->image_url = Storage::disk('uploads')->url($b->image)));

        $midBanner = Banner::where('is_active', true)->where('position', 'mid')->orderBy('sort_order')->first();
        if ($midBanner) $midBanner->image_url = Storage::disk('uploads')->url($midBanner->image);

        $activeFlashProductIds = FlashSale::active()->forCustomer()->pluck('product_id')->unique();
        $sourceProductIds = Product::withoutGlobalScopes()->whereIn('id', $activeFlashProductIds)->whereNotNull('source_product_id')->pluck('source_product_id');
        $allCustomerFlashIds = $activeFlashProductIds->merge($sourceProductIds)->unique();

        $flashSaleProducts = Product::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('status', 'active')
            ->where(function ($q) use ($allCustomerFlashIds) {
                $q->whereIn('id', $allCustomerFlashIds)
                  ->orWhereIn('source_product_id', $allCustomerFlashIds);
            })
            ->with(['category', 'activeFlashSale'])
            ->take(12)
            ->get();

        $homeContents = HomeContent::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $resellerContents = $homeContents->where('type', 'reseller')->values();
        $supplierContents = $homeContents->where('type', 'supplier')->values();

        return view('shop.home', compact('branch', 'categories', 'bestSelling', 'newArrivals', 'categoryShelves', 'stats', 'heroBanners', 'promo1Banner', 'promo2Banners', 'midBanner', 'flashSaleProducts', 'homeContents', 'resellerContents', 'supplierContents'));
    }
}
