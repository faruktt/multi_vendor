<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public const SESSION_KEY = 'reseller_cart';

    private function getCart(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    private function putCart(array $cart): void
    {
        Session::put(self::SESSION_KEY, $cart);
    }

    /** Add or increment a product in cart */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'qty'        => 'required|integer|min:1',
        ]);

        $website = \App\Models\Vendor::onlineStore();
        $product = Product::withoutGlobalScopes()->findOrFail($request->product_id);
        if ($product->supplier_id !== null || (int) $product->vendor_id !== (int) $website->id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Only Admin Website products are available for reseller purchase.'], 403);
            }
            return back()->with('error', 'Only Admin Website products are available for reseller purchase.');
        }

        $key = $request->product_id . '_' . ($request->variant_id ?? 'base');

        $cart = $this->getCart();
        $existing = $cart[$key]['qty'] ?? 0;
        $cart[$key] = [
            'product_id' => (int) $request->product_id,
            'variant_id' => $request->variant_id ? (int) $request->variant_id : null,
            'qty'        => $existing + (int) $request->qty,
        ];
        $this->putCart($cart);

        if ($request->wantsJson()) {
            return response()->json(['count' => count($cart), 'message' => 'Added to cart']);
        }

        if ($request->redirect_to === 'checkout') {
            return redirect()->route('reseller.orders.create');
        }

        return back()->with('success', 'Added to cart.');
    }

    /** Update quantity */
    public function update(Request $request)
    {
        $request->validate(['key' => 'required|string', 'qty' => 'required|integer|min:0']);

        $cart = $this->getCart();
        if ((int) $request->qty === 0) {
            unset($cart[$request->key]);
        } else {
            if (isset($cart[$request->key])) {
                $cart[$request->key]['qty'] = (int) $request->qty;
            }
        }
        $this->putCart($cart);

        return back()->with('success', 'Cart updated.');
    }

    /** Remove a line */
    public function remove(string $key)
    {
        $cart = $this->getCart();
        unset($cart[$key]);
        $this->putCart($cart);
        return back()->with('success', 'Item removed.');
    }

    /** Resolve raw cart to full lines with reseller price */
    public static function lines(): \Illuminate\Support\Collection
    {
        $cart = Session::get(self::SESSION_KEY, []);
        if (empty($cart)) return collect();

        $website    = \App\Models\Vendor::onlineStore();
        $productIds = collect($cart)->pluck('product_id')->unique()->filter();
        $products   = Product::withoutGlobalScopes()->whereNull('supplier_id')->where('vendor_id', $website->id)->whereIn('id', $productIds)->get()->keyBy('id');

        $variantIds = collect($cart)->pluck('variant_id')->filter()->unique();
        $variants   = ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');

        $lines = collect();
        foreach ($cart as $key => $item) {
            $product = $products->get($item['product_id'] ?? null);
            if (!$product) continue;

            $variant   = !empty($item['variant_id']) ? $variants->get($item['variant_id']) : null;
            $available = $variant ? $variant->stock_qty : $product->stock_qty;
            $qty       = min((int) ($item['qty'] ?? 0), max(0, $available));
            if ($qty <= 0) continue;

            // Use effective_reseller_price (discounted flash price if on reseller flash sale, else normal reseller_price)
            $price = (float) ($product->effective_reseller_price ?? ($variant?->price ?? $product->price));

            $lines->push([
                'key'                    => $key,
                'product'                => $product,
                'variant'                => $variant,
                'qty'                    => $qty,
                'price'                  => $price,
                'regular_reseller_price' => (float) ($product->reseller_price ?? ($variant?->price ?? $product->price)),
                'is_flash_sale'          => $product->is_on_reseller_flash_sale,
                'subtotal'               => $price * $qty,
                'image_url'              => $product->first_image_url ?? null,
            ]);
        }
        return $lines;
    }

    public static function count(): int
    {
        return count(Session::get(self::SESSION_KEY, []));
    }

    public static function forget(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function index()
    {
        $lines    = self::lines();
        $subtotal = $lines->sum('subtotal');
        return view('reseller.cart.index', compact('lines', 'subtotal'));
    }
}
