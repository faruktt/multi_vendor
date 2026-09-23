<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Cart
{
    private static function key(Vendor $branch): string
    {
        return 'shop_cart_' . $branch->id;
    }

    public static function raw(Vendor $branch): array
    {
        return session(self::key($branch), []);
    }

    public static function put(Vendor $branch, array $cart): void
    {
        session([self::key($branch) => $cart]);
    }

    public static function forget(Vendor $branch): void
    {
        session()->forget(self::key($branch));
    }

    /**
     * Resolve the raw session cart (product_id/variant_id/qty per line) against
     * current product data, clamping quantities to available stock and dropping
     * lines whose product/variant no longer exists, belongs to another branch, or is out of stock.
     */
    public static function resolve(Vendor $branch, array $cart): Collection
    {
        $productIds = collect($cart)->pluck('product_id')->unique()->filter();
        $products   = Product::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->whereIn('id', $productIds)
            ->get()->keyBy('id');

        $variantIds = collect($cart)->pluck('variant_id')->filter()->unique();
        $variants   = ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');

        $lines = collect();

        foreach ($cart as $key => $item) {
            $product = $products->get($item['product_id'] ?? null);
            if (!$product) continue;

            $variant = !empty($item['variant_id']) ? $variants->get($item['variant_id']) : null;

            $available = $variant ? $variant->stock_qty : $product->stock_qty;
            $qty = min((int) ($item['qty'] ?? 0), max(0, $available));
            if ($qty <= 0) continue;

            $basePrice = $product->effective_price;
            $price = (float) ($variant?->price ?? $basePrice);

            $lines->push([
                'key'           => $key,
                'product'       => $product,
                'variant'       => $variant,
                'qty'           => $qty,
                'price'         => $price,
                'regular_price' => (float) ($variant?->price ?? $product->price),
                'is_flash_sale' => $product->is_on_flash_sale,
                'subtotal'      => $price * $qty,
                'image_url'     => $product->first_image_url,
            ]);
        }

        return $lines;
    }

    public static function lines(Vendor $branch): Collection
    {
        return static::resolve($branch, static::raw($branch));
    }

    public static function total(Vendor $branch): float
    {
        return (float) static::lines($branch)->sum('subtotal');
    }

    public static function count(Vendor $branch): int
    {
        return static::lines($branch)->count();
    }
}
