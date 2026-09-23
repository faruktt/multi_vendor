<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use App\Support\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $branch = Vendor::onlineStore();

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'qty'        => 'nullable|integer|min:1',
        ]);

        $product = Product::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->findOrFail($request->product_id);

        $variant = $request->variant_id
            ? ProductVariant::where('product_id', $product->id)->findOrFail($request->variant_id)
            : null;

        $available = $variant ? $variant->stock_qty : $product->stock_qty;
        if ($available <= 0) {
            return back()->with('error', $product->name . ' is currently out of stock.');
        }

        $qty = max(1, (int) $request->input('qty', 1));
        $key = $product->id . '_' . ($variant?->id ?? 'base');

        $cart = Cart::raw($branch);
        $newQty = min(($cart[$key]['qty'] ?? 0) + $qty, $available);

        $cart[$key] = [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'qty'        => $newQty,
        ];
        Cart::put($branch, $cart);

        if ($request->input('redirect_to') === 'checkout') {
            return redirect()->route('shop.checkout.index');
        }

        return back()->with('success', $product->name . ' added to your cart.');
    }

    public function update(Request $request)
    {
        $branch = Vendor::onlineStore();

        $request->validate([
            'qty'   => 'required|array',
            'qty.*' => 'nullable|integer|min:0',
        ]);

        $cart = Cart::raw($branch);

        foreach ($request->input('qty', []) as $key => $qty) {
            if (!isset($cart[$key])) continue;

            $qty = (int) $qty;
            if ($qty <= 0) {
                unset($cart[$key]);
                continue;
            }

            $product = Product::withoutGlobalScopes()->where('vendor_id', $branch->id)->find($cart[$key]['product_id']);
            $variant = !empty($cart[$key]['variant_id']) ? ProductVariant::find($cart[$key]['variant_id']) : null;
            $available = $variant ? $variant?->stock_qty : $product?->stock_qty;

            $cart[$key]['qty'] = min($qty, max(0, (int) $available));
            if ($cart[$key]['qty'] <= 0) unset($cart[$key]);
        }

        Cart::put($branch, $cart);

        return $this->cartResponse($request, $branch, 'Cart updated.');
    }

    public function remove(Request $request, string $key)
    {
        $branch = Vendor::onlineStore();
        $cart = Cart::raw($branch);
        unset($cart[$key]);
        Cart::put($branch, $cart);

        return $this->cartResponse($request, $branch, 'Item removed from cart.');
    }

    private function cartResponse(Request $request, Vendor $branch, string $message)
    {
        if ($request->wantsJson()) {
            $lines = Cart::lines($branch);
            $subtotal = $lines->sum('subtotal');

            return response()->json([
                'count'    => $lines->count(),
                'subtotal' => $subtotal,
                'html'     => view('shop.partials.cart-drawer-body', [
                    'cartLines'    => $lines,
                    'cartSubtotal' => $subtotal,
                ])->render(),
                'checkout_lines_html' => view('shop.partials.checkout-cart-lines', [
                    'lines' => $lines,
                ])->render(),
            ]);
        }

        return back()->with('success', $message);
    }
}
