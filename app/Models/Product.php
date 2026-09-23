<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use BelongsToVendor;

    protected $fillable = [
        'vendor_id', 'supplier_id', 'category_id', 'source_product_id',
        'name', 'slug', 'sku', 'barcode', 'image', 'images',
        'description', 'short_description', 'facebook_video_url',
        'price', 'old_price', 'cost_price', 'reseller_price', 'stock_qty', 'unit', 'status',
        'approval_status', 'admin_commission_rate', 'rejection_reason', 'approved_at'
    ];

    protected $casts = [
        'price'                 => 'decimal:2',
        'old_price'             => 'decimal:2',
        'cost_price'            => 'decimal:2',
        'reseller_price'        => 'decimal:2',
        'admin_commission_rate' => 'decimal:2',
        'approved_at'           => 'datetime',
        'images'                => 'array',
    ];

    /** Returns all image filenames as an array (merges legacy single image + new images column). */
    public function getImagesListAttribute(): array
    {
        $list = $this->images ?? [];
        // Fall back to legacy single image field if images column is empty
        if (empty($list) && $this->image) {
            // Keep full relative path if legacy path contains a subfolder, e.g. products/filename.ext
            $list = [$this->image];
        }
        return array_values(array_filter($list));
    }

    /** URL for the first image (used for thumbnails). */
    public function getFirstImageUrlAttribute(): ?string
    {
        $urls = $this->image_urls;
        return $urls[0] ?? null;
    }

    /** Array of full URLs for all images. */
    public function getImageUrlsAttribute(): array
    {
        return array_map(function ($img) {
            if (str_contains($img, '/')) {
                return asset('uploads/' . $img);
            }
            return asset('uploads/products/' . $img);
        }, $this->images_list);
    }

    protected static function booted()
    {
        static::creating(function (Product $product) {
            if (!$product->slug) {
                $product->slug = static::uniqueSlug($product->vendor_id, $product->name);
            }
        });
    }

    public static function uniqueSlug(int $vendorId, string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $i = 2;

        while (
            static::withoutGlobalScopes()
                ->where('vendor_id', $vendorId)
                ->where('slug', $slug)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /** The warehouse product this branch product was cloned from, if any. */
    public function sourceProduct()
    {
        return $this->belongsTo(Product::class, 'source_product_id');
    }

    /** Branch clones created from this (warehouse) product via transfer. */
    public function branchClones()
    {
        return $this->hasMany(Product::class, 'source_product_id');
    }

    public function flashSales()
    {
        return $this->hasMany(FlashSale::class);
    }

    /**
     * Active flash sale specifically for customer storefront.
     */
    public function activeFlashSale()
    {
        return $this->hasOne(FlashSale::class)
            ->where('is_active', true)
            ->where('target_audience', 'customer')
            ->where(function ($q) {
                $now = now();
                $q->whereNull('start_time')->orWhere('start_time', '<=', $now);
            })
            ->where(function ($q) {
                $now = now();
                $q->whereNull('end_time')->orWhere('end_time', '>=', $now);
            });
    }

    /**
     * Active flash sale specifically for reseller portal.
     */
    public function activeResellerFlashSale()
    {
        return $this->hasOne(FlashSale::class)
            ->where('is_active', true)
            ->where('target_audience', 'reseller')
            ->where(function ($q) {
                $now = now();
                $q->whereNull('start_time')->orWhere('start_time', '<=', $now);
            })
            ->where(function ($q) {
                $now = now();
                $q->whereNull('end_time')->orWhere('end_time', '>=', $now);
            });
    }

    /**
     * Resolves the active customer flash sale record, checking directly or source lineage.
     */
    public function getActiveFlashSaleRecordAttribute(): ?FlashSale
    {
        if ($this->relationLoaded('activeFlashSale')) {
            if ($this->activeFlashSale) return $this->activeFlashSale;
        } else {
            $direct = $this->activeFlashSale()->first();
            if ($direct) return $direct;
        }

        if ($this->source_product_id) {
            $sourceSale = FlashSale::active()->where('target_audience', 'customer')->where('product_id', $this->source_product_id)->first();
            if ($sourceSale) return $sourceSale;
        } else {
            $childSale = FlashSale::active()
                ->where('target_audience', 'customer')
                ->whereHas('product', fn($q) => $q->where('source_product_id', $this->id))
                ->first();
            if ($childSale) return $childSale;
        }

        return null;
    }

    /**
     * Resolves the active reseller flash sale record, checking directly or source lineage.
     */
    public function getActiveResellerFlashSaleRecordAttribute(): ?FlashSale
    {
        if ($this->relationLoaded('activeResellerFlashSale')) {
            if ($this->activeResellerFlashSale) return $this->activeResellerFlashSale;
        } else {
            $direct = $this->activeResellerFlashSale()->first();
            if ($direct) return $direct;
        }

        if ($this->source_product_id) {
            $sourceSale = FlashSale::active()->where('target_audience', 'reseller')->where('product_id', $this->source_product_id)->first();
            if ($sourceSale) return $sourceSale;
        } else {
            $childSale = FlashSale::active()
                ->where('target_audience', 'reseller')
                ->whereHas('product', fn($q) => $q->where('source_product_id', $this->id))
                ->first();
            if ($childSale) return $childSale;
        }

        return null;
    }

    /**
     * Whether this product is currently on flash sale for customers.
     */
    public function isOnFlashSale(string $audience = 'customer'): bool
    {
        return $audience === 'reseller' 
            ? $this->isOnResellerFlashSale() 
            : ($this->active_flash_sale_record !== null);
    }

    public function isOnCustomerFlashSale(): bool
    {
        return $this->active_flash_sale_record !== null;
    }

    public function isOnResellerFlashSale(): bool
    {
        return $this->active_reseller_flash_sale_record !== null;
    }

    /**
     * Attribute alias: is_on_flash_sale (default customer storefront)
     */
    public function getIsOnFlashSaleAttribute(): bool
    {
        return $this->isOnCustomerFlashSale();
    }

    /**
     * Attribute alias: is_on_reseller_flash_sale
     */
    public function getIsOnResellerFlashSaleAttribute(): bool
    {
        return $this->isOnResellerFlashSale();
    }

    /**
     * Returns customer discounted flash price if active, or null.
     */
    public function getFlashPriceAttribute(): ?float
    {
        $sale = $this->active_flash_sale_record;
        return $sale ? (float) $sale->flash_price : null;
    }

    /**
     * Returns reseller discounted flash price if active, or null.
     */
    public function getResellerFlashPriceAttribute(): ?float
    {
        $sale = $this->active_reseller_flash_sale_record;
        return $sale ? (float) $sale->flash_price : null;
    }

    /**
     * The effective price customer pays (flash sale price if active, else regular price).
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->flash_price ?? (float) $this->price;
    }

    /**
     * Regular reseller baseline price (reseller_price if set, else price).
     */
    public function getRegularResellerPriceAttribute(): float
    {
        return (float) ($this->reseller_price ?? $this->price);
    }

    /**
     * The effective price reseller pays (reseller flash price if active, else regular reseller price).
     */
    public function getEffectiveResellerPriceAttribute(): float
    {
        return $this->reseller_flash_price ?? $this->regular_reseller_price;
    }

    /**
     * Current price alias (customer).
     */
    public function getCurrentPriceAttribute(): float
    {
        return $this->effective_price;
    }

    /**
     * Customer flash discount percentage.
     */
    public function getDiscountPercentageAttribute(): ?float
    {
        $sale = $this->active_flash_sale_record;
        if (!$sale) return null;
        if ($sale->discount_percentage) return (float) $sale->discount_percentage;
        $reg = (float) $this->price;
        if ($reg <= 0) return 0;
        return round((($reg - (float) $sale->flash_price) / $reg) * 100, 1);
    }

    /**
     * Reseller flash discount percentage compared to baseline reseller price.
     */
    public function getResellerDiscountPercentageAttribute(): ?float
    {
        $sale = $this->active_reseller_flash_sale_record;
        if (!$sale) return null;
        if ($sale->discount_percentage) return (float) $sale->discount_percentage;
        $reg = $this->regular_reseller_price;
        if ($reg <= 0) return 0;
        return round((($reg - (float) $sale->flash_price) / $reg) * 100, 1);
    }

    /**
     * Whether product has an old price higher than current price.
     */
    public function getHasOldPriceAttribute(): bool
    {
        return !empty($this->old_price) && (float) $this->old_price > (float) $this->price;
    }

    /**
     * Percentage discount from old_price to regular price.
     */
    public function getOldPriceDiscountPercentageAttribute(): ?float
    {
        if (!$this->has_old_price) {
            return null;
        }
        $old = (float) $this->old_price;
        $cur = (float) $this->price;
        if ($old <= 0) return 0;
        return round((($old - $cur) / $old) * 100);
    }

    public function isApproved(): bool
    {
        return ($this->approval_status ?? 'approved') === 'approved';
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    /**
     * Effective admin commission rate percentage for this product.
     * Prioritizes product's own admin_commission_rate, then supplier's commission_percentage, then 5%.
     */
    public function getEffectiveAdminCommissionRateAttribute(): float
    {
        if ($this->admin_commission_rate !== null) {
            return (float) $this->admin_commission_rate;
        }
        return (float) ($this->supplier?->commission_percentage ?? 5.00);
    }

    /**
     * Calculated commission amount per unit in currency (৳).
     */
    public function getAdminCommissionPerUnitAttribute(): float
    {
        $rate = $this->effective_admin_commission_rate;
        return round(((float) $this->price) * ($rate / 100), 2);
    }

    /**
     * Net earning supplier receives per unit in currency (৳).
     */
    public function getSupplierEarningPerUnitAttribute(): float
    {
        return round(((float) $this->price) - $this->admin_commission_per_unit, 2);
    }

    /**
     * Efficiently calculates and attaches total_purchased_qty and total_sold_qty
     * to a collection/paginator of products without N+1 queries.
     */
    public static function attachStockMetrics($products, ?int $vendorId = null)
    {
        $collection = $products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
            ? $products->getCollection()
            : (is_array($products) ? collect($products) : $products);

        if ($collection->isEmpty()) {
            return $products;
        }

        $productIds = $collection->pluck('id')->filter()->unique();
        $sourceIds  = $collection->pluck('source_product_id')->filter()->unique();
        $rootIds    = $productIds->merge($sourceIds)->unique();

        // Fetch all related products in the lineage
        $lineage = static::withoutGlobalScopes()
            ->whereIn('id', $rootIds)
            ->orWhereIn('source_product_id', $rootIds)
            ->get(['id', 'source_product_id', 'name', 'vendor_id']);

        $familyMap = [];
        $nameMap   = [];
        foreach ($collection as $p) {
            $rootId = $p->source_product_id ?: $p->id;
            $connected = $lineage->filter(fn($item) => $item->id == $rootId || $item->source_product_id == $rootId || $item->id == $p->id);
            $familyMap[$p->id] = $connected->pluck('id')->push($p->id)->unique()->toArray();
            $nameMap[$p->id]   = $p->name;
        }

        $allFamilyIds = collect($familyMap)->flatten()->unique()->toArray();

        // 1. Total purchases by product_id from PurchaseItem
        $purchasesByProd = PurchaseItem::whereIn('product_id', $allFamilyIds)
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->pluck('total_qty', 'product_id');

        // 2. Total purchases where product_id was null, matched by product_name
        $purchasesByName = PurchaseItem::whereNull('product_id')
            ->whereIn('product_name', array_values(array_filter($nameMap)))
            ->selectRaw('product_name, SUM(quantity) as total_qty')
            ->groupBy('product_name')
            ->pluck('total_qty', 'product_name');

        // 3. Purchase stock movements (in case recorded via movement)
        $movementsInByProd = StockMovement::withoutGlobalScopes()
            ->whereIn('product_id', $allFamilyIds)
            ->where('type', 'in')
            ->where('reference_type', 'purchase')
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->pluck('total_qty', 'product_id');

        // 4. Total sales by product_id from SaleItem
        $salesByProd = SaleItem::whereIn('product_id', $allFamilyIds)
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->pluck('total_qty', 'product_id');

        // 5. Sale stock movements (in case recorded via movement)
        $movementsOutByProd = StockMovement::withoutGlobalScopes()
            ->whereIn('product_id', $allFamilyIds)
            ->where('type', 'out')
            ->where('reference_type', 'sale')
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->pluck('total_qty', 'product_id');

        $isWarehouse = false;
        if ($vendorId) {
            $vendor = Vendor::find($vendorId);
            $isWarehouse = $vendor && $vendor->is_warehouse;
        }

        foreach ($collection as $p) {
            $fIds = $familyMap[$p->id] ?? [$p->id];

            // Calculate total purchased
            $piQty = 0;
            $smInQty = 0;
            foreach ($fIds as $fid) {
                $piQty += ($purchasesByProd[$fid] ?? 0);
                $smInQty += ($movementsInByProd[$fid] ?? 0);
            }
            if (!empty($nameMap[$p->id]) && isset($purchasesByName[$nameMap[$p->id]])) {
                $piQty += $purchasesByName[$nameMap[$p->id]];
            }

            $totalPurchased = max((int)$piQty, (int)$smInQty);

            // Calculate total sold
            $totalSold = 0;
            if ($vendorId && !$isWarehouse) {
                // Specific branch view: show sales for this branch's product
                $siQty = (int) ($salesByProd[$p->id] ?? 0);
                $smOut = (int) ($movementsOutByProd[$p->id] ?? 0);
                $totalSold = max($siQty, $smOut);
            } else {
                // Warehouse or global view: show total sold across lineage
                $siQty = 0;
                $smOut = 0;
                foreach ($fIds as $fid) {
                    $siQty += ($salesByProd[$fid] ?? 0);
                    $smOut += ($movementsOutByProd[$fid] ?? 0);
                }
                $totalSold = max((int)$siQty, (int)$smOut);
            }

            $p->total_purchased_qty = (int) $totalPurchased;
            $p->total_sold_qty      = (int) $totalSold;
        }

        return $products;
    }

    /** Accessor fallback for total_purchased_qty */
    public function getTotalPurchasedQtyAttribute(): int
    {
        if (isset($this->attributes['total_purchased_qty'])) {
            return (int) $this->attributes['total_purchased_qty'];
        }

        $rootId = $this->source_product_id ?: $this->id;
        $familyIds = static::withoutGlobalScopes()
            ->where('id', $rootId)
            ->orWhere('source_product_id', $rootId)
            ->pluck('id')
            ->push($this->id)
            ->unique();

        $pi = PurchaseItem::whereIn('product_id', $familyIds)->sum('quantity');
        if ($this->name) {
            $pi += PurchaseItem::whereNull('product_id')->where('product_name', $this->name)->sum('quantity');
        }
        $sm = StockMovement::withoutGlobalScopes()
            ->whereIn('product_id', $familyIds)
            ->where('type', 'in')
            ->where('reference_type', 'purchase')
            ->sum('quantity');

        return (int) max($pi, $sm);
    }

    /** Accessor fallback for total_sold_qty */
    public function getTotalSoldQtyAttribute(): int
    {
        if (isset($this->attributes['total_sold_qty'])) {
            return (int) $this->attributes['total_sold_qty'];
        }

        $si = $this->saleItems()->sum('quantity');
        $sm = StockMovement::withoutGlobalScopes()
            ->where('product_id', $this->id)
            ->where('type', 'out')
            ->where('reference_type', 'sale')
            ->sum('quantity');

        return (int) max($si, $sm);
    }
}

