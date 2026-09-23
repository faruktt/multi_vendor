<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vendor;
use App\Support\BangladeshLocations;
use App\Support\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $branch = Vendor::onlineStore();
        abort_unless($branch->status === 'active', 404);

        $lines = Cart::lines($branch);

        if ($lines->isEmpty()) {
            return redirect()->route('shop.products.index')->with('error', 'Your cart is empty.');
        }

        $subtotal = $lines->sum('subtotal');
        $customer = auth('customer')->user();

        // Location & Shipping settings
        $subDhakaDistricts = $branch->sub_dhaka_districts;
        if (is_string($subDhakaDistricts)) {
            $subDhakaDistricts = json_decode($subDhakaDistricts, true) ?: [];
        }
        if (empty($subDhakaDistricts) || !is_array($subDhakaDistricts)) {
            $subDhakaDistricts = BangladeshLocations::defaultSubDhakaDistricts();
        }

        $subDhakaThanas = $branch->sub_dhaka_thanas;
        if (is_string($subDhakaThanas)) {
            $subDhakaThanas = json_decode($subDhakaThanas, true) ?: [];
        }
        if (empty($subDhakaThanas) || !is_array($subDhakaThanas)) {
            $subDhakaThanas = BangladeshLocations::defaultSubDhakaThanas();
        }

        $allLocations    = BangladeshLocations::all();
        $allDistricts    = BangladeshLocations::districts();
        $deliveryInside  = (float) ($branch->delivery_charge_inside_dhaka ?? 60);
        $deliverySubDhaka= (float) ($branch->delivery_charge_sub_dhaka ?? 100);
        $deliveryOutside = (float) ($branch->delivery_charge_outside_dhaka ?? 150);

        return view('shop.checkout.index', compact(
            'branch', 'lines', 'subtotal', 'customer',
            'allLocations', 'allDistricts', 'subDhakaDistricts', 'subDhakaThanas',
            'deliveryInside', 'deliverySubDhaka', 'deliveryOutside'
        ));
    }

    /** Real-time AJAX validation for customer coupon */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code'  => 'required|string',
            'phone' => 'nullable|string',
        ]);

        $branch = Vendor::onlineStore();
        $lines = Cart::lines($branch);
        $subtotal = (float) $lines->sum('subtotal');

        if ($subtotal <= 0) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.']);
        }

        $code = strtoupper(trim($request->code));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.']);
        }

        $result = $coupon->validateFor($subtotal, 'customer', $request->phone);

        if (!$result['valid']) {
            return response()->json(['success' => false, 'message' => $result['message']]);
        }

        return response()->json([
            'success'         => true,
            'code'            => $coupon->code,
            'discount_amount' => $result['discount'],
            'discount_type'   => $coupon->discount_type,
            'message'         => $result['message'],
        ]);
    }

    public function store(Request $request)
    {
        $branch = Vendor::onlineStore();
        abort_unless($branch->status === 'active', 404);

        $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'required|string|max:30',
            'district'       => 'required|string|max:100',
            'thana'          => 'required|string|max:100',
            'address'        => 'required|string|max:500',
            'delivery_zone'  => 'required|in:inside,sub_dhaka,outside',
            'payment_method' => 'required|in:cash,bkash,nagad,card',
            'coupon_code'    => 'nullable|string',
        ]);

        $lines = Cart::lines($branch);
        if ($lines->isEmpty()) {
            return redirect()->route('shop.products.index')->with('error', 'Your cart is empty.');
        }

        $sale = DB::transaction(function () use ($request, $branch, $lines) {
            $customerData = [
                'name'     => $request->name,
                'phone'    => $request->phone,
                'address'  => $request->address,
                'district' => $request->district,
                'thana'    => $request->thana,
            ];

            if (auth('customer')->check()) {
                $customer = auth('customer')->user();
                $customer->update($customerData);
            } else {
                $customer = Customer::withoutGlobalScopes()->firstOrCreate(
                    ['vendor_id' => $branch->id, 'phone' => $request->phone],
                    $customerData
                );
                $customer->update($customerData);
            }

            // Determine zone & charge based on district, thana & store configuration
            $subDhakaThanas = $branch->sub_dhaka_thanas;
            if (is_string($subDhakaThanas)) {
                $subDhakaThanas = json_decode($subDhakaThanas, true) ?: [];
            }
            if (empty($subDhakaThanas) || !is_array($subDhakaThanas)) {
                $subDhakaThanas = BangladeshLocations::defaultSubDhakaThanas();
            }

            $district = trim($request->district);
            $thana    = trim($request->thana);
            $zone     = BangladeshLocations::determineZone($district, $thana, $subDhakaThanas);

            $deliveryCharge = match($zone) {
                'inside'    => (float) ($branch->delivery_charge_inside_dhaka ?? 60),
                'sub_dhaka' => (float) ($branch->delivery_charge_sub_dhaka ?? 100),
                default     => (float) ($branch->delivery_charge_outside_dhaka ?? 150),
            };

            $subtotal = (float) $lines->sum('subtotal');

            // Server-side coupon verification
            $discount = 0;
            $appliedCoupon = null;
            if ($request->filled('coupon_code')) {
                $coupon = Coupon::where('code', strtoupper(trim($request->coupon_code)))->first();
                if ($coupon) {
                    $validation = $coupon->validateFor($subtotal, 'customer', $request->phone);
                    if ($validation['valid']) {
                        $discount = $validation['discount'];
                        $appliedCoupon = $coupon;
                    }
                }
            }

            $total = max(0, $subtotal - $discount) + $deliveryCharge;
            $primarySupplierId = $lines->pluck('product.supplier_id')->filter()->first();

            $sale = Sale::create([
                'vendor_id'       => $branch->id,
                'customer_id'     => $customer->id,
                'supplier_id'     => $primarySupplierId,
                'coupon_id'       => $appliedCoupon?->id,
                'coupon_code'     => $appliedCoupon?->code,
                'invoice_no'      => Sale::generateInvoiceNo('INV'),
                'subtotal'        => $subtotal,
                'discount'        => $discount,
                'tax'             => 0,
                'district'        => $request->district,
                'thana'           => $request->thana,
                'delivery_zone'   => $zone,
                'delivery_charge' => $deliveryCharge,
                'total'           => $total,
                'paid_amount'     => 0,
                'due_amount'      => $total,
                'payment_status'  => 'pending',
                'payment_method'  => $request->payment_method,
                'order_status'    => 'pending',
                'channel'         => 'web',
                'created_by'      => $this->systemUserId($branch),
            ]);

            // Track coupon usage
            if ($appliedCoupon) {
                $appliedCoupon->increment('used_count');
                CouponUsage::create([
                    'coupon_id'       => $appliedCoupon->id,
                    'sale_id'         => $sale->id,
                    'customer_id'     => $customer->id,
                    'customer_phone'  => $request->phone,
                    'reseller_id'     => null,
                    'discount_amount' => $discount,
                ]);
            }

            foreach ($lines as $line) {
                $supplierId = $line['product']->supplier_id;
                $commRate   = 0.00;
                $commAmount = 0.00;
                $supplierNet = 0.00;

                if ($supplierId) {
                    $supplier    = $line['product']->supplier ?: \App\Models\Supplier::find($supplierId);
                    $commRate    = (float) ($line['product']->admin_commission_rate ?? $supplier?->commission_percentage ?? 5.00);
                    $commAmount  = round($line['subtotal'] * ($commRate / 100), 2);
                    $supplierNet = round($line['subtotal'] - $commAmount, 2);
                }

                SaleItem::create([
                    'sale_id'                 => $sale->id,
                    'product_id'              => $line['product']->id,
                    'supplier_id'             => $supplierId,
                    'admin_commission_rate'   => $commRate,
                    'admin_commission_amount' => $commAmount,
                    'supplier_earning'        => $supplierNet,
                    'product_variant_id'      => $line['variant']?->id,
                    'variant_name'            => $line['variant']?->variant_name,
                    'quantity'                => $line['qty'],
                    'unit_price'              => $line['price'],
                    'subtotal'                => $line['subtotal'],
                ]);

                if ($line['variant']) {
                    $line['variant']->decrement('stock_qty', $line['qty']);
                }
                Product::withoutGlobalScopes()->where('id', $line['product']->id)
                    ->decrement('stock_qty', $line['qty']);

                StockMovement::create([
                    'vendor_id'      => $branch->id,
                    'product_id'     => $line['product']->id,
                    'type'           => 'out',
                    'quantity'       => $line['qty'],
                    'reference_type' => 'sale',
                    'reference_id'   => $sale->id,
                    'note'           => 'Online order: ' . $sale->invoice_no,
                ]);
            }

            return $sale;
        });

        Cart::forget($branch);

        return redirect()->route('shop.checkout.success', $sale);
    }

    public function success(int $saleId)
    {
        $branch = Vendor::onlineStore();

        $sale = Sale::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->with(['saleItems.product', 'saleItems.variant', 'customer'])
            ->findOrFail($saleId);

        return view('shop.checkout.success', compact('branch', 'sale'));
    }

    /**
     * Orders placed via the storefront have no logged-in staff user, but
     * sales.created_by is a required FK — attribute them to the branch owner.
     */
    protected function systemUserId(Vendor $branch): int
    {
        return User::where('vendor_id', $branch->id)->value('id')
            ?? User::role('super-admin')->value('id');
    }
}
