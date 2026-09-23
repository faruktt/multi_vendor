<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Vendor;
use App\Support\BangladeshLocations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /** Show checkout form */
    public function create()
    {
        $lines    = CartController::lines();
        if ($lines->isEmpty()) {
            return redirect()->route('reseller.products.index')->with('error', 'Your cart is empty.');
        }
        $subtotal = $lines->sum('subtotal');

        $branch = Vendor::onlineStore() ?? Vendor::first();

        $subDhakaDistricts = $branch?->sub_dhaka_districts;
        if (is_string($subDhakaDistricts)) {
            $subDhakaDistricts = json_decode($subDhakaDistricts, true) ?: [];
        }
        if (empty($subDhakaDistricts) || !is_array($subDhakaDistricts)) {
            $subDhakaDistricts = BangladeshLocations::defaultSubDhakaDistricts();
        }

        $subDhakaThanas = $branch?->sub_dhaka_thanas;
        if (is_string($subDhakaThanas)) {
            $subDhakaThanas = json_decode($subDhakaThanas, true) ?: [];
        }
        if (empty($subDhakaThanas) || !is_array($subDhakaThanas)) {
            $subDhakaThanas = BangladeshLocations::defaultSubDhakaThanas();
        }

        $allLocations     = BangladeshLocations::all();
        $allDistricts     = BangladeshLocations::districts();
        $deliveryInside   = (float) ($branch?->delivery_charge_inside_dhaka ?? 60);
        $deliverySubDhaka = (float) ($branch?->delivery_charge_sub_dhaka ?? 100);
        $deliveryOutside  = (float) ($branch?->delivery_charge_outside_dhaka ?? 150);

        return view('reseller.orders.create', compact(
            'lines', 'subtotal',
            'allLocations', 'allDistricts', 'subDhakaDistricts', 'subDhakaThanas',
            'deliveryInside', 'deliverySubDhaka', 'deliveryOutside'
        ));
    }

    /** Real-time AJAX validation for reseller coupon */
    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $reseller = auth('reseller')->user();
        $lines    = CartController::lines();
        $subtotal = (float) $lines->sum('subtotal');

        if ($subtotal <= 0) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.']);
        }

        $code   = strtoupper(trim($request->code));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.']);
        }

        $result = $coupon->validateFor($subtotal, 'reseller', null, $reseller->id);

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

    /** Place the order */
    public function store(Request $request)
    {
        $reseller = auth('reseller')->user();
        $lines    = CartController::lines();

        if ($lines->isEmpty()) {
            return redirect()->route('reseller.products.index')->with('error', 'Your cart is empty.');
        }

        $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:30',
            'district'         => 'required|string|max:100',
            'thana'            => 'required|string|max:100',
            'address'          => 'required|string|max:500',
            'delivery_zone'    => 'required|in:inside,sub_dhaka,outside',
            'note'             => 'nullable|string|max:1000',
            'coupon_code'      => 'nullable|string',
            'selling_prices'   => 'required|array',
            'selling_prices.*' => 'required|numeric|min:0',
        ]);

        // Total reseller buy cost across all items
        $totalResellerCost = (float) $lines->sum('subtotal');

        // Server-side coupon verification
        $totalDiscount = 0;
        $appliedCoupon = null;
        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', strtoupper(trim($request->coupon_code)))->first();
            if ($coupon) {
                $validation = $coupon->validateFor($totalResellerCost, 'reseller', null, $reseller->id);
                if ($validation['valid']) {
                    $totalDiscount = $validation['discount'];
                    $appliedCoupon = $coupon;
                }
            }
        }

        // Determine zone & delivery charge
        $branch = Vendor::onlineStore() ?? Vendor::first();
        $subDhakaThanas = $branch?->sub_dhaka_thanas;
        if (is_string($subDhakaThanas)) {
            $subDhakaThanas = json_decode($subDhakaThanas, true) ?: [];
        }
        if (empty($subDhakaThanas) || !is_array($subDhakaThanas)) {
            $subDhakaThanas = BangladeshLocations::defaultSubDhakaThanas();
        }

        $district = trim($request->district);
        $thana    = trim($request->thana);
        $zone     = BangladeshLocations::determineZone($district, $thana, $subDhakaThanas);
        if ($request->filled('delivery_zone') && in_array($request->delivery_zone, ['inside', 'sub_dhaka', 'outside'])) {
            $zone = $request->delivery_zone;
        }

        $deliveryCharge = match($zone) {
            'inside'    => (float) ($branch?->delivery_charge_inside_dhaka ?? 60),
            'sub_dhaka' => (float) ($branch?->delivery_charge_sub_dhaka ?? 100),
            default     => (float) ($branch?->delivery_charge_outside_dhaka ?? 150),
        };

        // All products must belong to the same vendor (branch)
        // Group by vendor and create one sale per vendor
        $byVendor = $lines->groupBy(fn($l) => $l['product']->vendor_id);

        $sales = [];

        DB::transaction(function () use (
            $request, $reseller, $byVendor, $appliedCoupon,
            $totalDiscount, $totalResellerCost, $zone, $deliveryCharge, &$sales
        ) {
            $isFirstVendor = true;
            foreach ($byVendor as $vendorId => $vendorLines) {
                $vendorCustomerSubtotal = 0;
                $vendorResellerCost     = 0;
                $vendorResellerProfit   = 0;

                // Pre-calculate totals for this vendor's sale
                foreach ($vendorLines as $line) {
                    $customerPrice = $request->input('selling_prices.' . $line['key'], $line['price']);
                    $qty = $line['qty'];
                    $vendorCustomerSubtotal += ($customerPrice * $qty);
                    $vendorResellerCost     += ($line['price'] * $qty);
                    $vendorResellerProfit   += (($customerPrice - $line['price']) * $qty);
                }

                // Apportion coupon discount to this vendor if multiple vendors
                $vendorDiscount = 0;
                if ($totalDiscount > 0 && $totalResellerCost > 0) {
                    $vendorDiscount = round(($vendorResellerCost / $totalResellerCost) * $totalDiscount, 2);
                }

                // Coupon discount directly benefits the reseller's net profit!
                $netVendorProfit = $vendorResellerProfit + $vendorDiscount;

                // Allocate delivery charge to the first vendor sale to avoid double-charging if multiple vendors
                $vendorDeliveryCharge = $isFirstVendor ? $deliveryCharge : 0;
                $isFirstVendor = false;

                $vendorTotal = max(0, $vendorCustomerSubtotal - $vendorDiscount) + $vendorDeliveryCharge;

                // Create or update customer record for this vendor
                $customerData = [
                    'name'     => $request->name,
                    'phone'    => $request->phone,
                    'address'  => $request->address,
                    'district' => $request->district,
                    'thana'    => $request->thana,
                ];

                $customer = Customer::withoutGlobalScopes()->firstOrCreate(
                    ['vendor_id' => $vendorId, 'phone' => $request->phone],
                    $customerData
                );
                $customer->update($customerData);

                $sale = Sale::create([
                    'vendor_id'       => $vendorId,
                    'reseller_id'     => $reseller->id,
                    'customer_id'     => $customer->id,
                    'coupon_id'       => $appliedCoupon?->id,
                    'coupon_code'     => $appliedCoupon?->code,
                    'invoice_no'      => Sale::generateInvoiceNo('RES'),
                    'subtotal'        => $vendorCustomerSubtotal,
                    'discount'        => $vendorDiscount,
                    'tax'             => 0,
                    'district'        => $request->district,
                    'thana'           => $request->thana,
                    'delivery_zone'   => $zone,
                    'delivery_charge' => $vendorDeliveryCharge,
                    'total'           => $vendorTotal,
                    'paid_amount'     => 0,
                    'due_amount'      => $vendorTotal,
                    'payment_status'  => 'pending',
                    'payment_method'  => 'cash',
                    'order_status'    => 'pending',
                    'channel'         => 'reseller',
                    'created_by'      => null,
                    'note'            => $request->note ?: null,
                    'reseller_profit' => $netVendorProfit,
                ]);

                // Track coupon usage
                if ($appliedCoupon && $vendorDiscount > 0) {
                    CouponUsage::create([
                        'coupon_id'       => $appliedCoupon->id,
                        'sale_id'         => $sale->id,
                        'customer_id'     => $customer->id,
                        'customer_phone'  => $request->phone,
                        'reseller_id'     => $reseller->id,
                        'discount_amount' => $vendorDiscount,
                    ]);
                }

                foreach ($vendorLines as $line) {
                    $customerPrice = $request->input('selling_prices.' . $line['key'], $line['price']);
                    $itemCustomerSubtotal = $customerPrice * $line['qty'];
                    $itemResellerProfit = ($customerPrice - $line['price']) * $line['qty'];

                    SaleItem::create([
                        'sale_id'            => $sale->id,
                        'product_id'         => $line['product']->id,
                        'supplier_id'        => $line['product']->supplier_id,
                        'product_variant_id' => $line['variant']?->id,
                        'variant_name'       => $line['variant']?->variant_name,
                        'quantity'           => $line['qty'],
                        'unit_price'         => $customerPrice,
                        'subtotal'           => $itemCustomerSubtotal,
                        'reseller_buy_price' => $line['price'],
                        'reseller_profit'    => $itemResellerProfit,
                    ]);

                    Product::withoutGlobalScopes()->where('id', $line['product']->id)
                        ->decrement('stock_qty', $line['qty']);

                    if ($line['variant']) {
                        $line['variant']->decrement('stock_qty', $line['qty']);
                    }

                    StockMovement::create([
                        'vendor_id'      => $vendorId,
                        'product_id'     => $line['product']->id,
                        'type'           => 'out',
                        'quantity'       => $line['qty'],
                        'reference_type' => 'sale',
                        'reference_id'   => $sale->id,
                        'note'           => 'Reseller order: ' . $sale->invoice_no,
                    ]);
                }

                $sales[] = $sale;
            }
        });

        CartController::forget();

        return redirect()->route('reseller.orders.index')
            ->with('success', 'Order placed successfully! ' . count($sales) . ' order(s) created.');
    }

    /** List reseller's own orders */
    public function index(Request $request)
    {
        $reseller = auth('reseller')->user();

        $query = Sale::withoutGlobalScopes()
            ->where('reseller_id', $reseller->id)
            ->with(['saleItems.product', 'vendor', 'customer'])
            ->latest();

        if ($request->status) {
            $query->where('order_status', $request->status);
        }
        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('reseller.orders.index', compact('orders'));
    }

    /** Show single order */
    public function show(int $id)
    {
        $reseller = auth('reseller')->user();
        $order    = Sale::withoutGlobalScopes()
            ->where('reseller_id', $reseller->id)
            ->with(['saleItems.product', 'saleItems.variant', 'vendor', 'customer'])
            ->findOrFail($id);

        return view('reseller.orders.show', compact('order'));
    }

    /** Print invoice for reseller order */
    public function invoice(int $id)
    {
        $reseller = auth('reseller')->user();
        $order    = Sale::withoutGlobalScopes()
            ->where('reseller_id', $reseller->id)
            ->with(['saleItems.product', 'saleItems.variant', 'vendor', 'customer', 'reseller'])
            ->findOrFail($id);

        $barcodeSvg = null;
        try {
            if (class_exists(\Picqer\Barcode\BarcodeGeneratorSVG::class)) {
                $generator  = new \Picqer\Barcode\BarcodeGeneratorSVG();
                $cleanCode  = preg_replace('/[^A-Za-z0-9\-\_]/', '', $order->invoice_no);
                if ($cleanCode) {
                    $barcodeSvg = $generator->getBarcode($cleanCode, $generator::TYPE_CODE_128, 1.4, 32);
                }
            }
        } catch (\Throwable $e) {
            $barcodeSvg = null;
        }

        $appSettings = class_exists(\App\Helpers\AppSetting::class) ? \App\Helpers\AppSetting::all() : [];

        return view('reseller.orders.invoice', compact('order', 'reseller', 'barcodeSvg', 'appSettings'));
    }
}
