<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LandingPage;
use App\Models\LandingPageProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vendor;
use App\Support\BangladeshLocations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicLandingPageController extends Controller
{
    public function show(string $slug)
    {
        $landingPage = LandingPage::where('slug', $slug)->firstOrFail();

        // Allow preview for logged-in admin even if inactive
        $isAdmin = auth()->check() && (auth()->user()->hasRole('super-admin') || auth()->user()->can('manage_pages'));
        if ($landingPage->status !== 'active' && !$isAdmin) {
            abort(404, 'এই অফার পেজটি বর্তমানে সক্রিয় নেই।');
        }

        // Increment views
        $landingPage->increment('views_count');

        $branch = Vendor::onlineStore();
        $landingPage->load(['landingPageProducts.product.variants']);

        // Delivery Charges calculation
        $deliveryInside = $landingPage->free_shipping ? 0 : (
            $landingPage->delivery_charge_inside !== null ? (float) $landingPage->delivery_charge_inside : (
                isset($branch->delivery_charge_inside_dhaka) && $branch->delivery_charge_inside_dhaka !== ''
                    ? (float) $branch->delivery_charge_inside_dhaka
                    : 60.0
            )
        );

        $deliverySubDhaka = $landingPage->free_shipping ? 0 : (
            $landingPage->delivery_charge_sub_dhaka !== null ? (float) $landingPage->delivery_charge_sub_dhaka : (
                isset($branch->delivery_charge_sub_dhaka) && $branch->delivery_charge_sub_dhaka !== ''
                    ? (float) $branch->delivery_charge_sub_dhaka
                    : 100.0
            )
        );

        $deliveryOutside = $landingPage->free_shipping ? 0 : (
            $landingPage->delivery_charge_outside !== null ? (float) $landingPage->delivery_charge_outside : (
                isset($branch->delivery_charge_outside_dhaka) && $branch->delivery_charge_outside_dhaka !== ''
                    ? (float) $branch->delivery_charge_outside_dhaka
                    : 150.0
            )
        );

        $allLocations = BangladeshLocations::all();
        $allDistricts = BangladeshLocations::districts();

        return view('shop.landing.show', compact(
            'landingPage',
            'branch',
            'deliveryInside',
            'deliverySubDhaka',
            'deliveryOutside',
            'allLocations',
            'allDistricts'
        ));
    }

    public function order(Request $request, string $slug)
    {
        $landingPage = LandingPage::where('slug', $slug)->firstOrFail();
        $branch = Vendor::onlineStore();

        $request->validate([
            'package_id'    => 'required|exists:landing_page_products,id',
            'variant_id'    => 'nullable|exists:product_variants,id',
            'quantity'      => 'required|integer|min:1|max:50',
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|min:11|max:20',
            'address'       => 'required|string|max:500',
            'delivery_zone' => 'required|in:inside,sub_dhaka,outside',
            'district'      => 'nullable|string|max:100',
            'note'          => 'nullable|string|max:500',
        ]);

        $package = LandingPageProduct::with('product')->where('landing_page_id', $landingPage->id)->findOrFail($request->package_id);
        $product = $package->product;
        $variant = $request->filled('variant_id') ? ProductVariant::find($request->variant_id) : null;
        $quantity = (int) $request->quantity;

        // Delivery Charge
        $zone = $request->delivery_zone ?: 'inside';
        $deliveryCharge = 0.0;

        if (!$landingPage->free_shipping) {
            $deliveryCharge = match($zone) {
                'inside'    => $landingPage->delivery_charge_inside !== null ? (float) $landingPage->delivery_charge_inside : (
                    isset($branch->delivery_charge_inside_dhaka) && $branch->delivery_charge_inside_dhaka !== ''
                        ? (float) $branch->delivery_charge_inside_dhaka
                        : 60.0
                ),
                'sub_dhaka' => $landingPage->delivery_charge_sub_dhaka !== null ? (float) $landingPage->delivery_charge_sub_dhaka : (
                    isset($branch->delivery_charge_sub_dhaka) && $branch->delivery_charge_sub_dhaka !== ''
                        ? (float) $branch->delivery_charge_sub_dhaka
                        : 100.0
                ),
                default     => $landingPage->delivery_charge_outside !== null ? (float) $landingPage->delivery_charge_outside : (
                    isset($branch->delivery_charge_outside_dhaka) && $branch->delivery_charge_outside_dhaka !== ''
                        ? (float) $branch->delivery_charge_outside_dhaka
                        : 150.0
                ),
            };
        }

        // Unit Price
        $unitPrice = $package->effective_price;
        $subtotal = round($unitPrice * $quantity, 2);
        $total = round($subtotal + $deliveryCharge, 2);

        $sale = DB::transaction(function () use (
            $request, $landingPage, $branch, $package, $product, $variant, $quantity, $unitPrice, $subtotal, $deliveryCharge, $total, $zone
        ) {
            $district = trim($request->district ?? '');
            if (!$district) {
                $district = match($zone) {
                    'inside'    => 'Dhaka',
                    'sub_dhaka' => 'Dhaka Suburb',
                    'outside'   => 'Outside Dhaka',
                    default     => 'Dhaka',
                };
            }

            // Customer registration/lookup
            $customerData = [
                'name'     => $request->name,
                'phone'    => $request->phone,
                'address'  => $request->address,
                'district' => $district,
            ];

            $customer = Customer::withoutGlobalScopes()->firstOrCreate(
                ['vendor_id' => $branch->id, 'phone' => $request->phone],
                $customerData
            );
            $customer->update($customerData);

            $systemUserId = User::where('vendor_id', $branch->id)->value('id') ?? User::value('id');

            // Create Sale
            $sale = Sale::create([
                'vendor_id'       => $branch->id,
                'customer_id'     => $customer->id,
                'supplier_id'     => $product->supplier_id,
                'invoice_no'      => Sale::generateInvoiceNo('INV'),
                'subtotal'        => $subtotal,
                'discount'        => 0,
                'tax'             => 0,
                'district'        => $district,
                'delivery_zone'   => $zone,
                'delivery_charge' => $deliveryCharge,
                'total'           => $total,
                'paid_amount'     => 0,
                'due_amount'      => $total,
                'payment_status'  => 'pending',
                'payment_method'  => 'cash',
                'order_status'    => 'pending',
                'channel'         => 'landing',
                'created_by'      => $systemUserId,
                'note'            => "ল্যান্ডিং পেজ: {$landingPage->title}" . ($request->filled('note') ? " | নোট: " . $request->note : ""),
            ]);

            // Create SaleItem
            SaleItem::create([
                'sale_id'            => $sale->id,
                'product_id'         => $product->id,
                'supplier_id'        => $product->supplier_id,
                'product_variant_id' => $variant?->id,
                'variant_name'       => $variant?->variant_name ?? ($package->package_title ?: null),
                'quantity'           => $quantity,
                'unit_price'         => $unitPrice,
                'subtotal'           => $subtotal,
            ]);

            // Stock Movement
            try {
                StockMovement::create([
                    'vendor_id'          => $branch->id,
                    'product_id'         => $product->id,
                    'product_variant_id' => $variant?->id,
                    'type'               => 'out',
                    'quantity'           => $quantity,
                    'reference_type'     => 'sale',
                    'reference_id'       => $sale->id,
                    'note'               => "Landing Page Order #{$sale->invoice_no}",
                    'created_by'         => $systemUserId,
                ]);

                if ($product->stock_qty !== null) {
                    $product->decrement('stock_qty', $quantity);
                }
            } catch (\Throwable $e) {
                // Stock movement log optional failure recovery
            }

            // Increment landing page order count
            $landingPage->increment('orders_count');

            return $sale;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success'      => true,
                'redirect_url' => route('landing.success', ['slug' => $landingPage->slug, 'invoiceNo' => $sale->invoice_no]),
                'invoice_no'   => $sale->invoice_no,
            ]);
        }

        return redirect()->route('landing.success', [
            'slug'      => $landingPage->slug,
            'invoiceNo' => $sale->invoice_no,
        ])->with('success', 'আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে!');
    }

    public function success(string $slug, string $invoiceNo)
    {
        $landingPage = LandingPage::where('slug', $slug)->firstOrFail();
        $sale = Sale::with(['customer', 'saleItems.product', 'vendor'])
            ->where('invoice_no', $invoiceNo)
            ->firstOrFail();

        return view('shop.landing.success', compact('landingPage', 'sale'));
    }
}

