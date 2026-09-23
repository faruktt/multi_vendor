<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    private int $invoiceSeq = 1000;

    public function run(): void
    {
        $this->command->info('🧹 Cleaning previous demo data...');
        $this->cleanup();

        $this->command->info('🚀 Creating 5 demo vendors with full data...');

        $vendors = $this->vendorData();

        foreach ($vendors as $idx => $data) {
            $this->command->info("  → Creating vendor: {$data['vendor']['name']}");

            // 1. Create Vendor
            $vendor = Vendor::create($data['vendor']);

            // 2. Create Owner
            $owner = User::create([
                'name'      => $data['owner']['name'],
                'email'     => $data['owner']['email'],
                'password'  => Hash::make('password'),
                'vendor_id' => $vendor->id,
            ]);
            $owner->assignRole('vendor-owner');

            // 3. Create Staff
            foreach ($data['staff'] as $s) {
                $u = User::create([
                    'name'      => $s['name'],
                    'email'     => $s['email'],
                    'password'  => Hash::make('password'),
                    'vendor_id' => $vendor->id,
                ]);
                $u->assignRole($s['role']);
            }

            // 4. Create Categories
            $catMap = [];
            foreach ($data['categories'] as $catName) {
                $cat = Category::create([
                    'vendor_id' => $vendor->id,
                    'name'      => $catName,
                ]);
                $catMap[$catName] = $cat->id;
            }

            // 5. Create Products
            $productMap = [];
            foreach ($data['products'] as $p) {
                $product = Product::create([
                    'vendor_id'   => $vendor->id,
                    'category_id' => $catMap[$p['cat']],
                    'name'        => $p['name'],
                    'sku'         => 'SKU-' . strtoupper(Str::random(6)),
                    'barcode'     => sprintf('%04d%08d', $vendor->id, $this->invoiceSeq++),
                    'price'       => $p['price'],
                    'cost_price'  => $p['cost'],
                    'stock_qty'   => $p['stock'],
                    'unit'        => $p['unit'] ?? 'pcs',
                ]);
                $productMap[$p['name']] = $product;
            }

            // 6. Create Customers
            $customers = [];
            foreach ($data['customers'] as $c) {
                $customers[] = Customer::create([
                    'vendor_id' => $vendor->id,
                    'name'      => $c['name'],
                    'phone'     => $c['phone'],
                    'email'     => $c['email'] ?? null,
                    'address'   => $c['address'] ?? null,
                ]);
            }

            // 7. Create Sales (spread over last 45 days)
            $products = array_values($productMap);
            $this->createSales($vendor->id, $owner->id, $products, $customers, $data['salesCount']);

            $this->command->info("     ✓ {$data['salesCount']} sales created");
        }

        $this->command->info('✅ Demo data created successfully!');
        $this->command->newLine();
        $this->command->table(
            ['Vendor', 'Owner Email', 'Password'],
            array_map(fn($d) => [$d['vendor']['name'], $d['owner']['email'], 'password'], $vendors)
        );
    }

    /* ─── Cleanup previous demo data ────────────────── */
    private function cleanup(): void
    {
        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF');

        \Illuminate\Support\Facades\DB::table('sale_items')->delete();
        \Illuminate\Support\Facades\DB::table('sales')->delete();
        \Illuminate\Support\Facades\DB::table('stock_movements')->delete();
        \Illuminate\Support\Facades\DB::table('products')->delete();
        \Illuminate\Support\Facades\DB::table('customers')->delete();
        \Illuminate\Support\Facades\DB::table('categories')->delete();

        // Delete non-super-admin users and their tokens
        $vendorUserIds = User::whereNotNull('vendor_id')->pluck('id');
        \Illuminate\Support\Facades\DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->whereIn('tokenable_id', $vendorUserIds)
            ->delete();
        // Remove role assignments
        \Illuminate\Support\Facades\DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->whereIn('model_id', $vendorUserIds)
            ->delete();
        User::whereNotNull('vendor_id')->delete();

        \Illuminate\Support\Facades\DB::table('vendors')->delete();

        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON');
    }

    /* ─── Create realistic sales ─────────────────────── */
    private function createSales(int $vendorId, int $createdBy, array $products, array $customers, int $count): void
    {
        $payMethods = ['cash', 'cash', 'cash', 'bkash', 'nagad', 'card'];
        $orderStats = ['completed', 'completed', 'delivered', 'processing', 'pending', 'sent_to_courier'];

        for ($i = 0; $i < $count; $i++) {
            $date = Carbon::now()->subDays(rand(0, 45))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            $shuffled = $products;
            shuffle($shuffled);
            $cartItems = array_slice($shuffled, 0, rand(1, min(4, count($shuffled))));

            $subtotal = 0;
            $items    = [];
            foreach ($cartItems as $p) {
                $qty       = rand(1, 5);
                $price     = $p->price;
                $lineTotal = $qty * $price;
                $subtotal += $lineTotal;
                $items[]   = compact('qty', 'price', 'lineTotal', 'p');
            }

            $discount = rand(0, 3) === 0 ? round($subtotal * rand(5, 15) / 100) : 0;
            $total    = $subtotal - $discount;

            $payStatus  = $this->randomWeighted(['paid' => 60, 'partial' => 25, 'pending' => 15]);
            $paidAmount = match ($payStatus) {
                'paid'    => $total,
                'partial' => round($total * rand(30, 80) / 100),
                default   => 0,
            };
            $dueAmount = $total - $paidAmount;

            // Use DB::table to allow custom created_at timestamps
            $saleId = \Illuminate\Support\Facades\DB::table('sales')->insertGetId([
                'vendor_id'      => $vendorId,
                'customer_id'    => rand(0, 3) > 0 ? $customers[array_rand($customers)]->id : null,
                'invoice_no'     => 'INV-' . strtoupper(Str::random(8)),
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => 0,
                'total'          => $total,
                'paid_amount'    => $paidAmount,
                'due_amount'     => $dueAmount,
                'payment_status' => $payStatus,
                'payment_method' => $payMethods[array_rand($payMethods)],
                'order_status'   => $orderStats[array_rand($orderStats)],
                'created_by'     => $createdBy,
                'created_at'     => $date->toDateTimeString(),
                'updated_at'     => $date->toDateTimeString(),
            ]);

            $rows = [];
            foreach ($items as $item) {
                $rows[] = [
                    'sale_id'    => $saleId,
                    'product_id' => $item['p']->id,
                    'quantity'   => $item['qty'],
                    'unit_price' => $item['price'],
                    'subtotal'   => $item['lineTotal'],
                    'created_at' => $date->toDateTimeString(),
                    'updated_at' => $date->toDateTimeString(),
                ];
            }
            \Illuminate\Support\Facades\DB::table('sale_items')->insert($rows);
        }
    }

    private function randomWeighted(array $weights): string
    {
        $rand = rand(1, array_sum($weights));
        foreach ($weights as $val => $weight) {
            $rand -= $weight;
            if ($rand <= 0) return $val;
        }
        return array_key_first($weights);
    }

    /* ─── Vendor definitions ─────────────────────────── */
    private function vendorData(): array
    {
        return [

            /* ── 1. Rahman Electronics ── */
            [
                'vendor'     => ['name' => 'Rahman Electronics', 'owner_name' => 'Abdul Rahman', 'email' => 'rahman@shop.com', 'phone' => '01711-223344', 'address' => 'Elephant Road, Dhaka'],
                'owner'      => ['name' => 'Abdul Rahman',  'email' => 'rahman@shop.com'],
                'staff'      => [
                    ['name' => 'Karim Cashier', 'email' => 'karim@rahman.com',  'role' => 'cashier'],
                    ['name' => 'Rahim Staff',   'email' => 'rahim@rahman.com',   'role' => 'staff'],
                ],
                'categories' => ['Mobile Phones', 'Laptops', 'Accessories', 'TVs & Audio'],
                'products'   => [
                    ['name' => 'Samsung Galaxy A54 5G',    'price' => 32000,  'cost' => 28000,  'stock' => 15, 'cat' => 'Mobile Phones'],
                    ['name' => 'iPhone 14 128GB',           'price' => 115000, 'cost' => 100000, 'stock' => 5,  'cat' => 'Mobile Phones'],
                    ['name' => 'Xiaomi Redmi Note 12',      'price' => 18500,  'cost' => 15000,  'stock' => 22, 'cat' => 'Mobile Phones'],
                    ['name' => 'Realme C55',                'price' => 16000,  'cost' => 13000,  'stock' => 18, 'cat' => 'Mobile Phones'],
                    ['name' => 'Dell Inspiron 15 Core i5',  'price' => 68000,  'cost' => 60000,  'stock' => 8,  'cat' => 'Laptops'],
                    ['name' => 'HP Pavilion 14 Core i7',    'price' => 82000,  'cost' => 72000,  'stock' => 5,  'cat' => 'Laptops'],
                    ['name' => 'Asus VivoBook 15',          'price' => 55000,  'cost' => 48000,  'stock' => 7,  'cat' => 'Laptops'],
                    ['name' => 'USB-C Fast Charging Cable', 'price' => 350,    'cost' => 120,    'stock' => 150,'cat' => 'Accessories'],
                    ['name' => 'Samsung A54 Cover',         'price' => 450,    'cost' => 150,    'stock' => 80, 'cat' => 'Accessories'],
                    ['name' => 'Tempered Glass Screen Guard','price' => 250,   'cost' => 80,     'stock' => 100,'cat' => 'Accessories'],
                    ['name' => 'Bluetooth Earbuds TWS',     'price' => 1800,   'cost' => 1200,   'stock' => 30, 'cat' => 'Accessories'],
                    ['name' => 'Samsung 32" LED TV',        'price' => 28000,  'cost' => 24000,  'stock' => 10, 'cat' => 'TVs & Audio'],
                    ['name' => 'Sony Bravia 43" 4K',        'price' => 52000,  'cost' => 45000,  'stock' => 5,  'cat' => 'TVs & Audio'],
                ],
                'customers'  => [
                    ['name' => 'Md. Hasan Ali',     'phone' => '01811-100001', 'email' => 'hasan@gmail.com', 'address' => 'Mirpur, Dhaka'],
                    ['name' => 'Farzana Begum',     'phone' => '01911-100002', 'address' => 'Gulshan, Dhaka'],
                    ['name' => 'Jahangir Khan',     'phone' => '01611-100003', 'address' => 'Uttara, Dhaka'],
                    ['name' => 'Nasima Akter',      'phone' => '01511-100004'],
                    ['name' => 'Rafiqul Islam',     'phone' => '01711-100005', 'email' => 'rafiq@yahoo.com'],
                    ['name' => 'Sabbir Hossain',    'phone' => '01811-100006', 'address' => 'Dhanmondi, Dhaka'],
                    ['name' => 'Tahmina Khatun',    'phone' => '01911-100007'],
                ],
                'salesCount' => 45,
            ],

            /* ── 2. Sadia Fashion House ── */
            [
                'vendor'     => ['name' => 'Sadia Fashion House', 'owner_name' => 'Sadia Islam', 'email' => 'sadia@fashion.com', 'phone' => '01811-334455', 'address' => 'Agrabad, Chittagong'],
                'owner'      => ['name' => 'Sadia Islam',    'email' => 'sadia@fashion.com'],
                'staff'      => [
                    ['name' => 'Ritu Cashier',  'email' => 'ritu@sadia.com',   'role' => 'cashier'],
                    ['name' => 'Mitu Staff',    'email' => 'mitu@sadia.com',   'role' => 'staff'],
                ],
                'categories' => ['Ladies Dress', 'Men\'s Wear', 'Kids Collection', 'Footwear', 'Bags & Purses'],
                'products'   => [
                    ['name' => 'Georgette Saree Premium',   'price' => 2800,  'cost' => 1800,  'stock' => 30, 'cat' => 'Ladies Dress'],
                    ['name' => 'Cotton Kurti 3-piece',      'price' => 1500,  'cost' => 950,   'stock' => 45, 'cat' => 'Ladies Dress'],
                    ['name' => 'Silk Salwar Kameez',        'price' => 3500,  'cost' => 2400,  'stock' => 20, 'cat' => 'Ladies Dress'],
                    ['name' => 'Party Gown Western',        'price' => 5500,  'cost' => 3800,  'stock' => 12, 'cat' => 'Ladies Dress'],
                    ['name' => 'Men\'s Formal Shirt',       'price' => 1200,  'cost' => 750,   'stock' => 60, 'cat' => 'Men\'s Wear'],
                    ['name' => 'Men\'s Panjabi Premium',    'price' => 2200,  'cost' => 1500,  'stock' => 35, 'cat' => 'Men\'s Wear'],
                    ['name' => 'Denim Jeans Slim Fit',      'price' => 1800,  'cost' => 1100,  'stock' => 40, 'cat' => 'Men\'s Wear'],
                    ['name' => 'Kids Frock 2-5 Years',      'price' => 850,   'cost' => 500,   'stock' => 55, 'cat' => 'Kids Collection'],
                    ['name' => 'Baby Boy Set (3pc)',         'price' => 1100,  'cost' => 650,   'stock' => 40, 'cat' => 'Kids Collection'],
                    ['name' => 'Ladies Flat Sandal',        'price' => 950,   'cost' => 550,   'stock' => 50, 'cat' => 'Footwear'],
                    ['name' => 'Men\'s Leather Shoes',      'price' => 2500,  'cost' => 1700,  'stock' => 25, 'cat' => 'Footwear'],
                    ['name' => 'Ladies Handbag Premium',    'price' => 2200,  'cost' => 1400,  'stock' => 20, 'cat' => 'Bags & Purses'],
                    ['name' => 'Backpack School Bag',       'price' => 1500,  'cost' => 900,   'stock' => 30, 'cat' => 'Bags & Purses'],
                ],
                'customers'  => [
                    ['name' => 'Sumaiya Akter',     'phone' => '01811-200001', 'email' => 'sumaiya@gmail.com', 'address' => 'Pahartali, CTG'],
                    ['name' => 'Rekha Das',         'phone' => '01911-200002', 'address' => 'Nasirabad, CTG'],
                    ['name' => 'Amina Begum',       'phone' => '01611-200003'],
                    ['name' => 'Nadia Hossain',     'phone' => '01511-200004', 'email' => 'nadia@yahoo.com'],
                    ['name' => 'Shimul Akter',      'phone' => '01711-200005', 'address' => 'Oxygen, CTG'],
                    ['name' => 'Puja Chakrabarty',  'phone' => '01811-200006'],
                ],
                'salesCount' => 38,
            ],

            /* ── 3. Al-Shifa Pharmacy ── */
            [
                'vendor'     => ['name' => 'Al-Shifa Pharmacy', 'owner_name' => 'Dr. Anwar Hossain', 'email' => 'alshifa@pharmacy.com', 'phone' => '01911-445566', 'address' => 'Zindabazar, Sylhet'],
                'owner'      => ['name' => 'Dr. Anwar Hossain', 'email' => 'alshifa@pharmacy.com'],
                'staff'      => [
                    ['name' => 'Rony Cashier',   'email' => 'rony@alshifa.com',  'role' => 'cashier'],
                    ['name' => 'Limon Staff',    'email' => 'limon@alshifa.com',  'role' => 'staff'],
                ],
                'categories' => ['Antibiotics', 'Vitamins & Supplements', 'Pain Relief', 'Diabetic Care', 'Baby Care'],
                'products'   => [
                    ['name' => 'Azithromycin 500mg (6 tabs)',    'price' => 120,  'cost' => 80,   'stock' => 200, 'cat' => 'Antibiotics',           'unit' => 'pcs'],
                    ['name' => 'Amoxicillin 500mg (10 caps)',    'price' => 85,   'cost' => 55,   'stock' => 300, 'cat' => 'Antibiotics',           'unit' => 'pcs'],
                    ['name' => 'Ciprofloxacin 500mg (10 tabs)', 'price' => 95,   'cost' => 65,   'stock' => 250, 'cat' => 'Antibiotics',           'unit' => 'pcs'],
                    ['name' => 'Vitamin C 1000mg (30 tabs)',     'price' => 180,  'cost' => 120,  'stock' => 150, 'cat' => 'Vitamins & Supplements','unit' => 'pcs'],
                    ['name' => 'Vitamin D3 5000IU (30 caps)',    'price' => 250,  'cost' => 170,  'stock' => 120, 'cat' => 'Vitamins & Supplements','unit' => 'pcs'],
                    ['name' => 'Omega-3 Fish Oil (60 caps)',     'price' => 450,  'cost' => 300,  'stock' => 80,  'cat' => 'Vitamins & Supplements','unit' => 'pcs'],
                    ['name' => 'Paracetamol 500mg (10 tabs)',    'price' => 15,   'cost' => 8,    'stock' => 500, 'cat' => 'Pain Relief',           'unit' => 'pcs'],
                    ['name' => 'Ibuprofen 400mg (10 tabs)',      'price' => 25,   'cost' => 15,   'stock' => 400, 'cat' => 'Pain Relief',           'unit' => 'pcs'],
                    ['name' => 'Diclofenac Gel 50g',             'price' => 85,   'cost' => 55,   'stock' => 100, 'cat' => 'Pain Relief',           'unit' => 'pcs'],
                    ['name' => 'Metformin 500mg (30 tabs)',      'price' => 75,   'cost' => 50,   'stock' => 200, 'cat' => 'Diabetic Care',         'unit' => 'pcs'],
                    ['name' => 'Glucometer Test Strip (50 pcs)', 'price' => 650,  'cost' => 450,  'stock' => 60,  'cat' => 'Diabetic Care',         'unit' => 'pcs'],
                    ['name' => 'Diapers M Size (30 pcs)',        'price' => 550,  'cost' => 400,  'stock' => 80,  'cat' => 'Baby Care',             'unit' => 'pcs'],
                    ['name' => 'Baby Lotion 200ml',              'price' => 280,  'cost' => 190,  'stock' => 60,  'cat' => 'Baby Care',             'unit' => 'pcs'],
                ],
                'customers'  => [
                    ['name' => 'Mizanur Rahman',    'phone' => '01811-300001', 'email' => 'mizan@gmail.com', 'address' => 'Ambarkhana, Sylhet'],
                    ['name' => 'Khaleda Begum',     'phone' => '01911-300002', 'address' => 'Subhanighat, Sylhet'],
                    ['name' => 'Abu Taher',         'phone' => '01611-300003'],
                    ['name' => 'Shahnaz Parvin',    'phone' => '01511-300004'],
                    ['name' => 'Motiur Rahman',     'phone' => '01711-300005', 'email' => 'motiur@yahoo.com'],
                    ['name' => 'Ruksana Islam',     'phone' => '01811-300006', 'address' => 'Tilagarh, Sylhet'],
                    ['name' => 'Faruk Ahmed',       'phone' => '01911-300007'],
                    ['name' => 'Jhuma Dey',         'phone' => '01611-300008'],
                ],
                'salesCount' => 60,
            ],

            /* ── 4. City Grocery Mart ── */
            [
                'vendor'     => ['name' => 'City Grocery Mart', 'owner_name' => 'Monir Hossain', 'email' => 'citygrocery@mart.com', 'phone' => '01611-556677', 'address' => 'Shaheb Bazar, Rajshahi'],
                'owner'      => ['name' => 'Monir Hossain',  'email' => 'citygrocery@mart.com'],
                'staff'      => [
                    ['name' => 'Sujon Cashier', 'email' => 'sujon@citygrocery.com', 'role' => 'cashier'],
                    ['name' => 'Arif Staff',    'email' => 'arif@citygrocery.com',  'role' => 'staff'],
                    ['name' => 'Riya Cashier',  'email' => 'riya@citygrocery.com',  'role' => 'cashier'],
                ],
                'categories' => ['Rice & Grains', 'Oil & Ghee', 'Beverages', 'Snacks', 'Dairy & Eggs'],
                'products'   => [
                    ['name' => 'Miniket Rice 5kg',          'price' => 380,  'cost' => 320,  'stock' => 200, 'cat' => 'Rice & Grains', 'unit' => 'kg'],
                    ['name' => 'Nazirshail Rice 5kg',       'price' => 420,  'cost' => 360,  'stock' => 150, 'cat' => 'Rice & Grains', 'unit' => 'kg'],
                    ['name' => 'Atta Flour 2kg',            'price' => 130,  'cost' => 100,  'stock' => 300, 'cat' => 'Rice & Grains', 'unit' => 'pcs'],
                    ['name' => 'Soybean Oil 1L Rupchanda', 'price' => 175,  'cost' => 148,  'stock' => 250, 'cat' => 'Oil & Ghee',   'unit' => 'pcs'],
                    ['name' => 'Mustard Oil 1L',            'price' => 220,  'cost' => 185,  'stock' => 100, 'cat' => 'Oil & Ghee',   'unit' => 'pcs'],
                    ['name' => 'Ghee 400g Pran',            'price' => 480,  'cost' => 400,  'stock' => 60,  'cat' => 'Oil & Ghee',   'unit' => 'pcs'],
                    ['name' => 'Coca Cola 2L',              'price' => 120,  'cost' => 90,   'stock' => 200, 'cat' => 'Beverages',    'unit' => 'pcs'],
                    ['name' => 'Mango Juice 1L Pran',       'price' => 95,   'cost' => 68,   'stock' => 300, 'cat' => 'Beverages',    'unit' => 'pcs'],
                    ['name' => 'Mineral Water 1L Pure',     'price' => 20,   'cost' => 13,   'stock' => 500, 'cat' => 'Beverages',    'unit' => 'pcs'],
                    ['name' => 'Biscuit Digestive 400g',    'price' => 85,   'cost' => 60,   'stock' => 150, 'cat' => 'Snacks',       'unit' => 'pcs'],
                    ['name' => 'Chanachur 500g Bombay',     'price' => 75,   'cost' => 50,   'stock' => 100, 'cat' => 'Snacks',       'unit' => 'pcs'],
                    ['name' => 'Chips Lays 150g',           'price' => 55,   'cost' => 38,   'stock' => 200, 'cat' => 'Snacks',       'unit' => 'pcs'],
                    ['name' => 'Egg (12 pcs)',              'price' => 155,  'cost' => 130,  'stock' => 100, 'cat' => 'Dairy & Eggs', 'unit' => 'pcs'],
                    ['name' => 'Milk Full Cream 1L Arong',  'price' => 110,  'cost' => 88,   'stock' => 120, 'cat' => 'Dairy & Eggs', 'unit' => 'pcs'],
                    ['name' => 'Yogurt 400g Aarong',        'price' => 85,   'cost' => 65,   'stock' => 80,  'cat' => 'Dairy & Eggs', 'unit' => 'pcs'],
                ],
                'customers'  => [
                    ['name' => 'Belal Ahmed',       'phone' => '01811-400001', 'address' => 'Boalia, Rajshahi'],
                    ['name' => 'Shirin Akter',      'phone' => '01911-400002', 'email' => 'shirin@gmail.com'],
                    ['name' => 'Nurul Islam',       'phone' => '01611-400003', 'address' => 'Motihar, Rajshahi'],
                    ['name' => 'Kohinoor Begum',    'phone' => '01511-400004'],
                    ['name' => 'Abdus Salam',       'phone' => '01711-400005', 'email' => 'salam@yahoo.com'],
                    ['name' => 'Taslima Khanam',    'phone' => '01811-400006'],
                    ['name' => 'Jamal Uddin',       'phone' => '01911-400007', 'address' => 'Rajpara, Rajshahi'],
                ],
                'salesCount' => 80,
            ],

            /* ── 5. PC World BD ── */
            [
                'vendor'     => ['name' => 'PC World BD', 'owner_name' => 'Tanvir Ahmed', 'email' => 'pcworld@bd.com', 'phone' => '01511-667788', 'address' => 'KDA Avenue, Khulna'],
                'owner'      => ['name' => 'Tanvir Ahmed',   'email' => 'pcworld@bd.com'],
                'staff'      => [
                    ['name' => 'Rajib Cashier', 'email' => 'rajib@pcworld.com', 'role' => 'cashier'],
                    ['name' => 'Rubel Staff',   'email' => 'rubel@pcworld.com',  'role' => 'staff'],
                ],
                'categories' => ['Processors & RAM', 'Storage', 'Graphics & Display', 'Networking', 'Peripherals'],
                'products'   => [
                    ['name' => 'Intel Core i5-12th Gen',     'price' => 22000,  'cost' => 19000,  'stock' => 10, 'cat' => 'Processors & RAM'],
                    ['name' => 'AMD Ryzen 5 5600',           'price' => 18500,  'cost' => 15500,  'stock' => 12, 'cat' => 'Processors & RAM'],
                    ['name' => 'RAM DDR4 8GB 3200MHz',       'price' => 3200,   'cost' => 2600,   'stock' => 30, 'cat' => 'Processors & RAM'],
                    ['name' => 'RAM DDR4 16GB Kingston',     'price' => 5800,   'cost' => 4900,   'stock' => 20, 'cat' => 'Processors & RAM'],
                    ['name' => 'SSD 512GB Samsung 870',      'price' => 7500,   'cost' => 6500,   'stock' => 20, 'cat' => 'Storage'],
                    ['name' => 'HDD 1TB Seagate Barracuda',  'price' => 4200,   'cost' => 3600,   'stock' => 25, 'cat' => 'Storage'],
                    ['name' => 'SSD NVMe 1TB WD Black',      'price' => 11000,  'cost' => 9500,   'stock' => 15, 'cat' => 'Storage'],
                    ['name' => 'GTX 1650 4GB GDDR6',         'price' => 28000,  'cost' => 24000,  'stock' => 8,  'cat' => 'Graphics & Display'],
                    ['name' => 'Monitor 24" FHD IPS 75Hz',   'price' => 16500,  'cost' => 14000,  'stock' => 10, 'cat' => 'Graphics & Display'],
                    ['name' => 'TP-Link Router AC1200',      'price' => 3500,   'cost' => 2800,   'stock' => 20, 'cat' => 'Networking'],
                    ['name' => 'Gigabit Switch 8-port',      'price' => 2200,   'cost' => 1700,   'stock' => 15, 'cat' => 'Networking'],
                    ['name' => 'Mechanical Keyboard RGB',    'price' => 3800,   'cost' => 2900,   'stock' => 20, 'cat' => 'Peripherals'],
                    ['name' => 'Gaming Mouse Logitech G102', 'price' => 2500,   'cost' => 1900,   'stock' => 25, 'cat' => 'Peripherals'],
                    ['name' => 'USB Hub 7-Port 3.0',         'price' => 850,    'cost' => 550,    'stock' => 40, 'cat' => 'Peripherals'],
                    ['name' => 'Webcam 1080p USB',           'price' => 2200,   'cost' => 1700,   'stock' => 15, 'cat' => 'Peripherals'],
                ],
                'customers'  => [
                    ['name' => 'Sabbir Ahmed',      'phone' => '01811-500001', 'email' => 'sabbir@gmail.com', 'address' => 'Sonadanga, Khulna'],
                    ['name' => 'Mahbub Alam',       'phone' => '01911-500002', 'address' => 'Daulatpur, Khulna'],
                    ['name' => 'Rashed Khan',       'phone' => '01611-500003'],
                    ['name' => 'Limon Tech',        'phone' => '01511-500004', 'email' => 'limon@tech.com'],
                    ['name' => 'Monjur Hossain',    'phone' => '01711-500005', 'address' => 'Khalishpur, Khulna'],
                    ['name' => 'Biplab Das',        'phone' => '01811-500006', 'email' => 'biplab@yahoo.com'],
                ],
                'salesCount' => 35,
            ],

        ];
    }
}
