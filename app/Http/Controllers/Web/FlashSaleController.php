<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;

class FlashSaleController extends Controller
{
    public function index(Request $request)
    {
        $query = FlashSale::with(['product.category', 'product.vendor', 'createdBy'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('end_time', '<', now());
            }
        }

        if ($request->filled('target_audience') && in_array($request->target_audience, ['customer', 'reseller'])) {
            $query->where('target_audience', $request->target_audience);
        }

        $flashSales = $query->paginate(15)->withQueryString();

        // Products for dropdown:
        // Include products available in online store or active catalog
        $onlineVendor = Vendor::onlineStore();
        $products = Product::withoutGlobalScopes()
            ->where('status', 'active')
            ->where(function ($q) use ($onlineVendor) {
                $q->where('vendor_id', $onlineVendor?->id)
                  ->orWhereNull('source_product_id');
            })
            ->with('category')
            ->orderBy('name')
            ->get();

        // Summary stats
        $allDeals = FlashSale::with('product')->get();
        $activeDeals = $allDeals->filter(fn($f) => $f->isCurrentlyActive());
        $expiredDeals = $allDeals->filter(fn($f) => $f->end_time && now()->gt($f->end_time));

        $customerDeals = $activeDeals->filter(fn($f) => ($f->target_audience ?? 'customer') === 'customer');
        $resellerDeals = $activeDeals->filter(fn($f) => $f->target_audience === 'reseller');

        $totalSavings = $activeDeals->sum(fn($f) => $f->discount_amount);
        $avgDiscount = $activeDeals->count() > 0 ? round($activeDeals->avg('discount_percentage'), 1) : 0;

        $stats = [
            'total_deals'    => $allDeals->count(),
            'active_deals'   => $activeDeals->count(),
            'customer_deals' => $customerDeals->count(),
            'reseller_deals' => $resellerDeals->count(),
            'expired_deals'  => $expiredDeals->count(),
            'total_savings'  => $totalSavings,
            'avg_discount'   => $avgDiscount,
        ];

        return view('admin.flash-sales.index', compact('flashSales', 'products', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'      => 'required|exists:products,id',
            'target_audience' => 'required|in:customer,reseller',
            'flash_price'     => 'required|numeric|min:0.01',
            'start_time'      => 'nullable|date',
            'end_time'        => 'nullable|date|after_or_equal:start_time',
            'is_active'       => 'nullable|boolean',
        ]);

        $product = Product::withoutGlobalScopes()->findOrFail($request->product_id);
        $targetAudience = $request->input('target_audience', 'customer');

        // Determine baseline price based on target audience
        if ($targetAudience === 'reseller') {
            $basePrice = (float) ($product->reseller_price ?? $product->price);
            $audienceLabel = 'Reseller';
            $priceLabel = 'reseller price';
        } else {
            $basePrice = (float) $product->price;
            $audienceLabel = 'Customer';
            $priceLabel = 'regular retail price';
        }

        if ((float) $request->flash_price >= $basePrice) {
            return back()->withInput()->with('error', "Flash sale price (৳{$request->flash_price}) must be less than the {$priceLabel} (৳{$basePrice}).");
        }

        $discountPct = $basePrice > 0 ? max(0, round((($basePrice - (float) $request->flash_price) / $basePrice) * 100, 2)) : 0;

        // Upsert flash sale specifically for (product_id, target_audience)
        $flashSale = FlashSale::updateOrCreate(
            [
                'product_id'      => $product->id,
                'target_audience' => $targetAudience,
            ],
            [
                'flash_price'         => $request->flash_price,
                'discount_percentage' => $discountPct,
                'start_time'          => $request->start_time ?: now(),
                'end_time'            => $request->end_time,
                'is_active'           => $request->has('is_active') ? $request->boolean('is_active') : true,
                'created_by'          => auth()->id(),
            ]
        );

        return redirect()->route('admin.flash-sales.index')->with('success', "⚡ {$audienceLabel} Flash Sale active for '{$product->name}' at ৳" . number_format($request->flash_price, 0) . " ({$discountPct}% OFF).");
    }

    public function update(Request $request, FlashSale $flashSale)
    {
        $request->validate([
            'target_audience' => 'nullable|in:customer,reseller',
            'flash_price'     => 'required|numeric|min:0.01',
            'start_time'      => 'nullable|date',
            'end_time'        => 'nullable|date|after_or_equal:start_time',
            'is_active'       => 'nullable|boolean',
        ]);

        $product = $flashSale->product;
        $targetAudience = $request->input('target_audience', $flashSale->target_audience ?? 'customer');

        if ($targetAudience === 'reseller') {
            $basePrice = (float) ($product?->reseller_price ?? ($product?->price ?? 0));
            $priceLabel = 'reseller price';
        } else {
            $basePrice = (float) ($product?->price ?? 0);
            $priceLabel = 'regular retail price';
        }

        if ($basePrice > 0 && (float) $request->flash_price >= $basePrice) {
            return back()->withInput()->with('error', "Flash sale price must be less than the {$priceLabel} (৳{$basePrice}).");
        }

        $discountPct = $basePrice > 0 ? max(0, round((($basePrice - (float) $request->flash_price) / $basePrice) * 100, 2)) : 0;

        $flashSale->update([
            'target_audience'     => $targetAudience,
            'flash_price'         => $request->flash_price,
            'discount_percentage' => $discountPct,
            'start_time'          => $request->start_time ?: now(),
            'end_time'            => $request->end_time,
            'is_active'           => $request->has('is_active') ? $request->boolean('is_active') : $flashSale->is_active,
        ]);

        return redirect()->route('admin.flash-sales.index')->with('success', 'Flash Sale updated successfully.');
    }

    public function toggleStatus(FlashSale $flashSale)
    {
        $flashSale->update(['is_active' => !$flashSale->is_active]);

        $statusText = $flashSale->is_active ? 'activated' : 'deactivated';

        if (request()->wantsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => $flashSale->is_active,
                'message'   => "Flash sale {$statusText}.",
            ]);
        }

        return back()->with('success', "Flash sale {$statusText}.");
    }

    public function destroy(FlashSale $flashSale)
    {
        $name = $flashSale->product?->name ?? 'Product';
        $flashSale->delete();

        return redirect()->route('admin.flash-sales.index')->with('success', "Flash sale for '{$name}' removed.");
    }
}
