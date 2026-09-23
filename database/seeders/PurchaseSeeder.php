<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::all();

        $supplierNames = [
            ['name' => 'ABC Trading Co.',       'phone' => '01711-100001', 'email' => 'abc@trading.com'],
            ['name' => 'Dhaka Wholesale Ltd.',   'phone' => '01711-100002', 'email' => 'dhaka@wholesale.com'],
            ['name' => 'Mega Suppliers BD',      'phone' => '01711-100003', 'email' => 'mega@suppliers.com'],
            ['name' => 'Prime Distributors',     'phone' => '01711-100004', 'email' => 'prime@dist.com'],
            ['name' => 'Galaxy Importers',       'phone' => '01711-100005', 'email' => 'galaxy@import.com'],
        ];

        foreach ($vendors as $vendor) {
            $owner = User::where('vendor_id', $vendor->id)->first();
            if (!$owner) continue;

            $products = Product::withoutGlobalScopes()
                ->where('vendor_id', $vendor->id)
                ->get();

            if ($products->isEmpty()) continue;

            // Create 3-4 suppliers per vendor
            $suppliers = collect($supplierNames)->take(rand(3, 4))->map(fn($s, $i) =>
                Supplier::create([
                    'vendor_id' => $vendor->id,
                    'name'      => $s['name'],
                    'phone'     => $s['phone'],
                    'email'     => $s['email'],
                    'address'   => 'Dhaka, Bangladesh',
                ])
            );

            // Create 12 purchases spread over last 6 months
            for ($i = 0; $i < 12; $i++) {
                $daysAgo  = rand(1, 180);
                $supplier = $suppliers->random();
                $status   = collect(['paid', 'paid', 'paid', 'partial', 'pending'])->random();

                // Pick 2-4 random products
                $pickedProducts = $products->random(min(rand(2, 4), $products->count()));

                $items    = $pickedProducts->map(fn($p) => [
                    'product'  => $p,
                    'qty'      => rand(5, 50),
                    'cost'     => round($p->price * rand(55, 75) / 100, 2), // cost = 55-75% of sale price
                ]);

                $subtotal = $items->sum(fn($i) => $i['qty'] * $i['cost']);
                $discount = rand(0, 1) ? round($subtotal * rand(2, 8) / 100, 2) : 0;
                $total    = $subtotal - $discount;

                $paid = match ($status) {
                    'paid'    => $total,
                    'partial' => round($total * rand(30, 70) / 100, 2),
                    default   => 0,
                };
                $due = max(0, $total - $paid);

                $purchase = Purchase::create([
                    'vendor_id'      => $vendor->id,
                    'supplier_id'    => $supplier->id,
                    'created_by'     => $owner->id,
                    'invoice_no'     => 'PUR-' . strtoupper(Str::random(8)),
                    'subtotal'       => $subtotal,
                    'discount'       => $discount,
                    'total'          => $total,
                    'paid_amount'    => $paid,
                    'due_amount'     => $due,
                    'payment_status' => $status,
                    'payment_method' => collect(['cash', 'bank', 'cheque'])->random(),
                    'note'           => null,
                    'created_at'     => now()->subDays($daysAgo),
                    'updated_at'     => now()->subDays($daysAgo),
                ]);

                foreach ($items as $item) {
                    PurchaseItem::create([
                        'purchase_id'  => $purchase->id,
                        'product_id'   => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'quantity'     => $item['qty'],
                        'unit_cost'    => $item['cost'],
                        'subtotal'     => $item['qty'] * $item['cost'],
                    ]);

                    // Add stock
                    Product::withoutGlobalScopes()
                        ->where('id', $item['product']->id)
                        ->increment('stock_qty', $item['qty']);

                    StockMovement::create([
                        'vendor_id'      => $vendor->id,
                        'product_id'     => $item['product']->id,
                        'type'           => 'in',
                        'quantity'       => $item['qty'],
                        'reference_type' => 'purchase',
                        'reference_id'   => $purchase->id,
                        'note'           => 'Purchase: ' . $purchase->invoice_no,
                        'created_at'     => $purchase->created_at,
                        'updated_at'     => $purchase->created_at,
                    ]);
                }
            }
        }
    }
}
