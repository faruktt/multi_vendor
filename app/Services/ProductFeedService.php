<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Vendor;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductFeedService
{
    /**
     * Get a fair, randomized & interleaved feed of products across active suppliers and direct admin/store.
     *
     * Example distribution per refresh:
     * - Supplier 1 (1-2 products)
     * - Supplier 2 (1-2 products)
     * - Admin / Store (1-2 products)
     * - Supplier 3 (1-2 products)
     * - On refresh: random order reshuffles with fresh selection!
     *
     * @param Vendor|int $branch
     * @param int|null $categoryId
     * @param int $limit
     * @param string|null $search
     * @param float|null $minPrice
     * @param float|null $maxPrice
     * @return Collection
     */
    public static function getBalancedFeed(
        Vendor|int $branch,
        ?int $categoryId = null,
        int $limit = 8,
        ?string $search = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): Collection {
        $branchId = is_object($branch) ? $branch->id : $branch;

        $buildQuery = function () use ($branchId, $categoryId, $search, $minPrice, $maxPrice) {
            $q = Product::withoutGlobalScopes()
                ->where('vendor_id', $branchId)
                ->where('status', 'active')
                ->where(function ($sub) {
                    $sub->whereNull('supplier_id')
                        ->orWhere('approval_status', 'approved');
                })
                ->with(['category', 'supplier', 'variants.color', 'variants.size']);

            if ($categoryId) {
                $q->where('category_id', $categoryId);
            }

            if ($search) {
                $s = trim($search);
                $q->where(function ($sub) use ($s) {
                    $sub->where('name', 'like', "%{$s}%")
                        ->orWhere('sku', 'like', "%{$s}%");
                });
            }

            if ($minPrice !== null) {
                $q->where('price', '>=', $minPrice);
            }

            if ($maxPrice !== null) {
                $q->where('price', '<=', $maxPrice);
            }

            return $q;
        };

        // 1. Check if Admin (supplier_id IS NULL) has active products in this scope
        $hasAdmin = $buildQuery()->whereNull('supplier_id')->exists();

        // 2. Discover all active approved Suppliers with products in this scope
        $supplierIds = $buildQuery()
            ->whereNotNull('supplier_id')
            ->pluck('supplier_id')
            ->unique()
            ->values()
            ->all();

        if (!empty($supplierIds)) {
            // Verify active status of suppliers
            $supplierIds = Supplier::whereIn('id', $supplierIds)
                ->where('status', 'active')
                ->pluck('id')
                ->all();
        }

        // 3. Assemble all seller buckets (null = admin, integer = supplier ID)
        $buckets = [];
        if ($hasAdmin) {
            $buckets[] = null; // Admin
        }
        foreach ($supplierIds as $sId) {
            $buckets[] = (int) $sId;
        }

        // If no products match at all, return empty collection
        if (empty($buckets)) {
            return collect();
        }

        // 4. Randomize the bucket order on each request / refresh
        shuffle($buckets);

        // 5. From each bucket, pick 1 to 2 random products
        $bucketProducts = [];
        foreach ($buckets as $bucket) {
            $bQuery = $buildQuery();
            if ($bucket === null) {
                $bQuery->whereNull('supplier_id');
            } else {
                $bQuery->where('supplier_id', $bucket);
            }

            // Pick 1 to 2 products randomly
            $pickCount = mt_rand(1, 2);
            $items = $bQuery->inRandomOrder()->take($pickCount)->get();
            if ($items->isNotEmpty()) {
                $bucketProducts[] = $items->values();
            }
        }

        // 6. Interleave products fairly (Round 1: 1st item from each; Round 2: 2nd item from each)
        $interleaved = collect();
        $selectedIds = [];
        $maxPerBucket = 2;

        for ($i = 0; $i < $maxPerBucket; $i++) {
            foreach ($bucketProducts as $products) {
                if (isset($products[$i])) {
                    $item = $products[$i];
                    if (!in_array($item->id, $selectedIds)) {
                        $interleaved->push($item);
                        $selectedIds[] = $item->id;
                    }
                }
            }
        }

        // 7. If we still need more products to reach $limit, backfill randomly from remaining products
        if ($interleaved->count() < $limit) {
            $remainingNeeded = $limit - $interleaved->count();
            $backfill = $buildQuery()
                ->whereNotIn('id', $selectedIds)
                ->inRandomOrder()
                ->take($remainingNeeded)
                ->get();

            foreach ($backfill as $item) {
                $interleaved->push($item);
            }
        }

        // 8. Return up to the requested limit
        return $interleaved->take($limit)->values();
    }

    /**
     * Get a paginated balanced random feed for the Shop catalog page.
     *
     * @param Vendor|int $branch
     * @param int|null $categoryId
     * @param int $perPage
     * @param int $page
     * @param string|null $search
     * @param float|null $minPrice
     * @param float|null $maxPrice
     * @return LengthAwarePaginator
     */
    public static function getBalancedPaginatedFeed(
        Vendor|int $branch,
        ?int $categoryId = null,
        int $perPage = 20,
        int $page = 1,
        ?string $search = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): LengthAwarePaginator {
        $branchId = is_object($branch) ? $branch->id : $branch;

        $totalCount = Product::withoutGlobalScopes()
            ->where('vendor_id', $branchId)
            ->where('status', 'active')
            ->where(function ($sub) {
                $sub->whereNull('supplier_id')
                    ->orWhere('approval_status', 'approved');
            })
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($search, function ($q) use ($search) {
                $s = trim($search);
                $q->where(fn($sub) => $sub->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%"));
            })
            ->when($minPrice !== null, fn($q) => $q->where('price', '>=', $minPrice))
            ->when($maxPrice !== null, fn($q) => $q->where('price', '<=', $maxPrice))
            ->count();

        $items = self::getBalancedFeed($branch, $categoryId, $perPage, $search, $minPrice, $maxPrice);

        return new LengthAwarePaginator(
            $items,
            $totalCount,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
