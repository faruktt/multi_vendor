<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\IncomeExpense;
use App\Models\IncomeExpenseCategory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Vendor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Ensure roles exist ────────────────────────────────
        foreach (['super-admin', 'vendor-owner', 'cashier', 'staff'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // ── Super Admin ────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('admin123'), 'vendor_id' => null]
        );
        if (!$admin->hasRole('super-admin')) $admin->assignRole('super-admin');

        // ── 5 Demo Branches ────────────────────────────────────
        $branches = [
            [
                'vendor'  => ['name' => 'Mirpur Electronics Hub', 'owner_name' => 'Rafiqul Islam',    'email' => 'mirpur@pos.com',      'phone' => '01711000001', 'address' => 'Mirpur-10, Dhaka'],
                'owner'   => ['name' => 'Rafiqul Islam',    'email' => 'owner.mirpur@pos.com',    'password' => 'demo1234'],
                'cashier' => ['name' => 'Riya Sultana',     'email' => 'cashier.mirpur@pos.com',  'password' => 'demo1234'],
                'categories' => ['Phones & Tablets', 'Laptops & PCs', 'Accessories', 'Audio & Video', 'Cables & Chargers'],
                'products' => [
                    ['name' => 'Samsung Galaxy A55',   'cat' => 'Phones & Tablets',  'price' => 45000, 'cost' => 38000, 'stock' => 15,  'unit' => 'pcs'],
                    ['name' => 'Redmi Note 13',         'cat' => 'Phones & Tablets',  'price' => 22000, 'cost' => 18000, 'stock' => 20,  'unit' => 'pcs'],
                    ['name' => 'iPhone 15 Case',        'cat' => 'Accessories',       'price' => 800,   'cost' => 350,   'stock' => 80,  'unit' => 'pcs'],
                    ['name' => 'Wireless Earbuds',      'cat' => 'Audio & Video',     'price' => 2500,  'cost' => 1500,  'stock' => 40,  'unit' => 'pcs'],
                    ['name' => 'USB-C Cable 1m',        'cat' => 'Cables & Chargers', 'price' => 250,   'cost' => 100,   'stock' => 200, 'unit' => 'pcs'],
                    ['name' => 'Power Bank 20000mAh',   'cat' => 'Cables & Chargers', 'price' => 2800,  'cost' => 1800,  'stock' => 30,  'unit' => 'pcs'],
                    ['name' => 'Laptop Bag 15"',        'cat' => 'Accessories',       'price' => 1500,  'cost' => 800,   'stock' => 25,  'unit' => 'pcs'],
                    ['name' => 'Bluetooth Speaker',     'cat' => 'Audio & Video',     'price' => 3500,  'cost' => 2200,  'stock' => 18,  'unit' => 'pcs'],
                    ['name' => 'Screen Protector',      'cat' => 'Accessories',       'price' => 300,   'cost' => 100,   'stock' => 150, 'unit' => 'pcs'],
                    ['name' => 'Car Charger 30W',       'cat' => 'Cables & Chargers', 'price' => 450,   'cost' => 200,   'stock' => 60,  'unit' => 'pcs'],
                    ['name' => 'Laptop Asus VivoBook',  'cat' => 'Laptops & PCs',     'price' => 55000, 'cost' => 45000, 'stock' => 8,   'unit' => 'pcs'],
                    ['name' => 'Keyboard Wireless',     'cat' => 'Laptops & PCs',     'price' => 1800,  'cost' => 900,   'stock' => 22,  'unit' => 'pcs'],
                ],
                'customers' => [
                    ['name' => 'Imran Hossain',   'phone' => '01911100001', 'address' => 'Mirpur-1, Dhaka'],
                    ['name' => 'Farida Khatun',   'phone' => '01911100002', 'address' => 'Mirpur-6, Dhaka'],
                    ['name' => 'Alamin Mia',      'phone' => '01911100003', 'address' => 'Mirpur-10, Dhaka'],
                    ['name' => 'Sabina Yasmin',   'phone' => '01911100004', 'address' => 'Pallabi, Dhaka'],
                    ['name' => 'Torikul Islam',   'phone' => '01911100005', 'address' => 'Kazipara, Dhaka'],
                    ['name' => 'Roksana Begum',   'phone' => '01911100006', 'address' => 'Shewrapara, Dhaka'],
                    ['name' => 'Jahangir Alam',   'phone' => '01911100007', 'address' => 'Mirpur-12, Dhaka'],
                    ['name' => 'Poly Akter',      'phone' => '01911100008', 'address' => 'Matikata, Dhaka'],
                ],
                'suppliers' => [
                    ['name' => 'Tech Wholesale BD',  'phone' => '01811100001', 'address' => 'Elephant Road, Dhaka'],
                    ['name' => 'Mobile Galaxy',      'phone' => '01811100002', 'address' => 'IDB Bhaban, Agargaon'],
                    ['name' => 'Gadget Zone',        'phone' => '01811100003', 'address' => 'Multiplan Centre, Dhaka'],
                ],
            ],
            [
                'vendor'  => ['name' => 'Dhanmondi Fashion House', 'owner_name' => 'Nusrat Jahan',    'email' => 'dhanmondi@pos.com',   'phone' => '01711000002', 'address' => 'Dhanmondi-27, Dhaka'],
                'owner'   => ['name' => 'Nusrat Jahan',    'email' => 'owner.dhanmondi@pos.com',  'password' => 'demo1234'],
                'cashier' => ['name' => 'Mehedi Hasan',    'email' => 'cashier.dhanmondi@pos.com','password' => 'demo1234'],
                'categories' => ["Men's Wear", "Women's Wear", "Kids' Wear", 'Footwear', 'Bags & Accessories'],
                'products' => [
                    ['name' => 'Men\'s Formal Shirt',    'cat' => "Men's Wear",       'price' => 850,   'cost' => 450,   'stock' => 60,  'unit' => 'pcs'],
                    ['name' => 'Men\'s Chino Pants',     'cat' => "Men's Wear",       'price' => 1200,  'cost' => 650,   'stock' => 45,  'unit' => 'pcs'],
                    ['name' => 'Women\'s Kurti',         'cat' => "Women's Wear",     'price' => 750,   'cost' => 380,   'stock' => 80,  'unit' => 'pcs'],
                    ['name' => 'Women\'s Saree Georgette','cat' => "Women's Wear",    'price' => 2500,  'cost' => 1400,  'stock' => 25,  'unit' => 'pcs'],
                    ['name' => 'Kids\' T-Shirt',         'cat' => "Kids' Wear",       'price' => 400,   'cost' => 200,   'stock' => 100, 'unit' => 'pcs'],
                    ['name' => 'Kids\' Jeans',           'cat' => "Kids' Wear",       'price' => 650,   'cost' => 330,   'stock' => 55,  'unit' => 'pcs'],
                    ['name' => 'Sports Sneakers',        'cat' => 'Footwear',         'price' => 1800,  'cost' => 900,   'stock' => 30,  'unit' => 'pair'],
                    ['name' => 'Ladies Sandal',          'cat' => 'Footwear',         'price' => 1200,  'cost' => 600,   'stock' => 40,  'unit' => 'pair'],
                    ['name' => 'Leather Handbag',        'cat' => 'Bags & Accessories','price' => 2200, 'cost' => 1200,  'stock' => 20,  'unit' => 'pcs'],
                    ['name' => 'Canvas Backpack',        'cat' => 'Bags & Accessories','price' => 950,  'cost' => 480,   'stock' => 35,  'unit' => 'pcs'],
                    ['name' => 'Men\'s Polo T-Shirt',    'cat' => "Men's Wear",       'price' => 600,   'cost' => 300,   'stock' => 70,  'unit' => 'pcs'],
                    ['name' => 'Fashion Belt',           'cat' => 'Bags & Accessories','price' => 450,  'cost' => 200,   'stock' => 50,  'unit' => 'pcs'],
                ],
                'customers' => [
                    ['name' => 'Sumaiya Akter',    'phone' => '01911200001', 'address' => 'Dhanmondi-2, Dhaka'],
                    ['name' => 'Karim Molla',      'phone' => '01911200002', 'address' => 'Dhanmondi-15, Dhaka'],
                    ['name' => 'Rashida Begum',    'phone' => '01911200003', 'address' => 'Lalmatia, Dhaka'],
                    ['name' => 'Tanvir Ahmed',     'phone' => '01911200004', 'address' => 'Dhanmondi-27, Dhaka'],
                    ['name' => 'Mou Rani Das',     'phone' => '01911200005', 'address' => 'Azimpur, Dhaka'],
                    ['name' => 'Shahjahan Khan',   'phone' => '01911200006', 'address' => 'Zigatola, Dhaka'],
                    ['name' => 'Fatema Khanom',    'phone' => '01911200007', 'address' => 'Hatirpool, Dhaka'],
                    ['name' => 'Robin Mia',        'phone' => '01911200008', 'address' => 'Dhanmondi-32, Dhaka'],
                ],
                'suppliers' => [
                    ['name' => 'Fashion World BD',   'phone' => '01811200001', 'address' => 'Gausia Market, Dhaka'],
                    ['name' => 'Style Hub Dhaka',    'phone' => '01811200002', 'address' => 'New Market, Dhaka'],
                    ['name' => 'Kids Corner Supply', 'phone' => '01811200003', 'address' => 'Bashundhara City, Dhaka'],
                ],
            ],
            [
                'vendor'  => ['name' => 'Uttara Grocery Mart', 'owner_name' => 'Shafiqul Alam',    'email' => 'uttara@pos.com',      'phone' => '01711000003', 'address' => 'Uttara Sector-7, Dhaka'],
                'owner'   => ['name' => 'Shafiqul Alam',   'email' => 'owner.uttara@pos.com',    'password' => 'demo1234'],
                'cashier' => ['name' => 'Shahana Parvin',  'email' => 'cashier.uttara@pos.com',  'password' => 'demo1234'],
                'categories' => ['Rice & Grains', 'Oil & Spices', 'Dairy & Eggs', 'Snacks & Beverages', 'Household'],
                'products' => [
                    ['name' => 'Miniket Rice 5kg',     'cat' => 'Rice & Grains',    'price' => 380,  'cost' => 300,  'stock' => 500, 'unit' => 'bag'],
                    ['name' => 'Lentils (Masoor) 1kg', 'cat' => 'Rice & Grains',    'price' => 130,  'cost' => 100,  'stock' => 300, 'unit' => 'kg'],
                    ['name' => 'Soybean Oil 5L',       'cat' => 'Oil & Spices',     'price' => 950,  'cost' => 780,  'stock' => 150, 'unit' => 'bottle'],
                    ['name' => 'Mixed Spice Pack',     'cat' => 'Oil & Spices',     'price' => 120,  'cost' => 80,   'stock' => 200, 'unit' => 'pack'],
                    ['name' => 'Milk 1L (Fresh)',      'cat' => 'Dairy & Eggs',     'price' => 80,   'cost' => 62,   'stock' => 200, 'unit' => 'liter'],
                    ['name' => 'Eggs (Dozen)',         'cat' => 'Dairy & Eggs',     'price' => 145,  'cost' => 118,  'stock' => 300, 'unit' => 'dozen'],
                    ['name' => 'Butter 200g',          'cat' => 'Dairy & Eggs',     'price' => 220,  'cost' => 175,  'stock' => 100, 'unit' => 'pcs'],
                    ['name' => 'Mineral Water 1.5L',   'cat' => 'Snacks & Beverages','price' => 30,  'cost' => 18,   'stock' => 500, 'unit' => 'bottle'],
                    ['name' => 'Energy Drink 250ml',   'cat' => 'Snacks & Beverages','price' => 75,  'cost' => 50,   'stock' => 300, 'unit' => 'can'],
                    ['name' => 'Potato Chips 150g',    'cat' => 'Snacks & Beverages','price' => 60,  'cost' => 40,   'stock' => 250, 'unit' => 'pack'],
                    ['name' => 'Noodles Masala',       'cat' => 'Snacks & Beverages','price' => 25,  'cost' => 16,   'stock' => 400, 'unit' => 'pcs'],
                    ['name' => 'Dish Wash Liquid 500ml','cat' => 'Household',        'price' => 110, 'cost' => 75,   'stock' => 200, 'unit' => 'bottle'],
                    ['name' => 'Toilet Cleaner 500ml', 'cat' => 'Household',        'price' => 90,  'cost' => 60,   'stock' => 180, 'unit' => 'bottle'],
                ],
                'customers' => [
                    ['name' => 'Habiba Khatun',    'phone' => '01911300001', 'address' => 'Uttara Sector-3, Dhaka'],
                    ['name' => 'Monoar Hossain',   'phone' => '01911300002', 'address' => 'Uttara Sector-6, Dhaka'],
                    ['name' => 'Dilruba Akter',    'phone' => '01911300003', 'address' => 'Uttara Sector-9, Dhaka'],
                    ['name' => 'Anwar Hossain',    'phone' => '01911300004', 'address' => 'Uttara Sector-11, Dhaka'],
                    ['name' => 'Jasmin Ara',       'phone' => '01911300005', 'address' => 'Diabari, Uttara'],
                    ['name' => 'Belal Ahmed',      'phone' => '01911300006', 'address' => 'Abdullahpur, Dhaka'],
                    ['name' => 'Minara Begum',     'phone' => '01911300007', 'address' => 'Uttara Sector-14, Dhaka'],
                    ['name' => 'Zakir Hossain',    'phone' => '01911300008', 'address' => 'Azampur, Uttara'],
                ],
                'suppliers' => [
                    ['name' => 'Agro Fresh BD',       'phone' => '01811300001', 'address' => 'Kawran Bazar, Dhaka'],
                    ['name' => 'Daily Needs Wholesale','phone' => '01811300002', 'address' => 'Badda, Dhaka'],
                    ['name' => 'Food Corner Supply',  'phone' => '01811300003', 'address' => 'Uttara, Dhaka'],
                ],
            ],
            [
                'vendor'  => ['name' => 'Gulshan Pharmacy & Health', 'owner_name' => 'Dr. Hasan Khan',  'email' => 'gulshan@pos.com',     'phone' => '01711000004', 'address' => 'Gulshan-1, Dhaka'],
                'owner'   => ['name' => 'Dr. Hasan Khan',  'email' => 'owner.gulshan@pos.com',   'password' => 'demo1234'],
                'cashier' => ['name' => 'Lima Begum',      'email' => 'cashier.gulshan@pos.com', 'password' => 'demo1234'],
                'categories' => ['Medicines', 'Vitamins & Supplements', 'Skin Care', 'Baby Care', 'Medical Equipment'],
                'products' => [
                    ['name' => 'Paracetamol 500mg (Strip)','cat' => 'Medicines',           'price' => 25,   'cost' => 15,   'stock' => 500, 'unit' => 'strip'],
                    ['name' => 'Antacid Syrup 100ml',      'cat' => 'Medicines',           'price' => 95,   'cost' => 65,   'stock' => 200, 'unit' => 'bottle'],
                    ['name' => 'Vitamin C 500mg (30 tabs)','cat' => 'Vitamins & Supplements','price' => 250, 'cost' => 160,  'stock' => 150, 'unit' => 'box'],
                    ['name' => 'Fish Oil Capsule (60 caps)','cat' => 'Vitamins & Supplements','price' => 450,'cost' => 300,  'stock' => 100, 'unit' => 'box'],
                    ['name' => 'Zinc 20mg (30 tabs)',      'cat' => 'Vitamins & Supplements','price' => 180, 'cost' => 110,  'stock' => 120, 'unit' => 'box'],
                    ['name' => 'Face Wash Oil Control',    'cat' => 'Skin Care',            'price' => 280,  'cost' => 170,  'stock' => 80,  'unit' => 'pcs'],
                    ['name' => 'Moisturizer SPF50',        'cat' => 'Skin Care',            'price' => 580,  'cost' => 350,  'stock' => 60,  'unit' => 'pcs'],
                    ['name' => 'Sunscreen Lotion 60ml',    'cat' => 'Skin Care',            'price' => 320,  'cost' => 190,  'stock' => 70,  'unit' => 'pcs'],
                    ['name' => 'Baby Diaper (M 30pcs)',    'cat' => 'Baby Care',            'price' => 850,  'cost' => 650,  'stock' => 60,  'unit' => 'pack'],
                    ['name' => 'Baby Lotion 200ml',        'cat' => 'Baby Care',            'price' => 380,  'cost' => 240,  'stock' => 50,  'unit' => 'bottle'],
                    ['name' => 'Digital Thermometer',      'cat' => 'Medical Equipment',   'price' => 450,  'cost' => 280,  'stock' => 40,  'unit' => 'pcs'],
                    ['name' => 'Blood Pressure Monitor',   'cat' => 'Medical Equipment',   'price' => 2800, 'cost' => 1900, 'stock' => 15,  'unit' => 'pcs'],
                ],
                'customers' => [
                    ['name' => 'Amena Khatun',     'phone' => '01911400001', 'address' => 'Gulshan-1, Dhaka'],
                    ['name' => 'Siraj Uddin',      'phone' => '01911400002', 'address' => 'Gulshan-2, Dhaka'],
                    ['name' => 'Farzana Islam',    'phone' => '01911400003', 'address' => 'Banani, Dhaka'],
                    ['name' => 'Mostafa Kamal',    'phone' => '01911400004', 'address' => 'Niketan, Dhaka'],
                    ['name' => 'Rubina Parvin',    'phone' => '01911400005', 'address' => 'DOHS Baridhara'],
                    ['name' => 'Ashik Rahman',     'phone' => '01911400006', 'address' => 'Gulshan-1, Dhaka'],
                    ['name' => 'Sufia Begum',      'phone' => '01911400007', 'address' => 'Gulshan Avenue'],
                    ['name' => 'Nazmul Karim',     'phone' => '01911400008', 'address' => 'Badda, Dhaka'],
                ],
                'suppliers' => [
                    ['name' => 'MediCare Wholesale',   'phone' => '01811400001', 'address' => 'Nayapaltan, Dhaka'],
                    ['name' => 'Pharma World BD',      'phone' => '01811400002', 'address' => 'Motijheel, Dhaka'],
                    ['name' => 'Health Plus Supply',   'phone' => '01811400003', 'address' => 'Segunbagicha, Dhaka'],
                ],
            ],
            [
                'vendor'  => ['name' => 'Mohammadpur General Store', 'owner_name' => 'Akbar Ali',      'email' => 'mohammadpur@pos.com', 'phone' => '01711000005', 'address' => 'Mohammadpur Bus Stand, Dhaka'],
                'owner'   => ['name' => 'Akbar Ali',       'email' => 'owner.mohammadpur@pos.com',  'password' => 'demo1234'],
                'cashier' => ['name' => 'Nazma Khatun',    'email' => 'cashier.mohammadpur@pos.com','password' => 'demo1234'],
                'categories' => ['Stationery', 'Home & Kitchen', 'Sports & Fitness', 'Toys & Games', 'Cosmetics & Beauty'],
                'products' => [
                    ['name' => 'A4 Paper Ream (500 sheets)','cat' => 'Stationery',         'price' => 420,  'cost' => 320,  'stock' => 100, 'unit' => 'ream'],
                    ['name' => 'Ball Pen Blue (12pcs)',      'cat' => 'Stationery',         'price' => 120,  'cost' => 75,   'stock' => 200, 'unit' => 'box'],
                    ['name' => 'Notebook A5 (200 pages)',    'cat' => 'Stationery',         'price' => 95,   'cost' => 55,   'stock' => 150, 'unit' => 'pcs'],
                    ['name' => 'Non-stick Fry Pan 28cm',     'cat' => 'Home & Kitchen',     'price' => 1200, 'cost' => 750,  'stock' => 25,  'unit' => 'pcs'],
                    ['name' => 'Dinner Set 6 Person',        'cat' => 'Home & Kitchen',     'price' => 2500, 'cost' => 1600, 'stock' => 15,  'unit' => 'set'],
                    ['name' => 'Stainless Steel Flask 1L',   'cat' => 'Home & Kitchen',     'price' => 650,  'cost' => 380,  'stock' => 40,  'unit' => 'pcs'],
                    ['name' => 'Yoga Mat',                   'cat' => 'Sports & Fitness',   'price' => 850,  'cost' => 480,  'stock' => 30,  'unit' => 'pcs'],
                    ['name' => 'Resistance Band Set',        'cat' => 'Sports & Fitness',   'price' => 450,  'cost' => 250,  'stock' => 45,  'unit' => 'set'],
                    ['name' => 'Skipping Rope',              'cat' => 'Sports & Fitness',   'price' => 280,  'cost' => 130,  'stock' => 60,  'unit' => 'pcs'],
                    ['name' => 'Lego Building Set',          'cat' => 'Toys & Games',       'price' => 1800, 'cost' => 1100, 'stock' => 20,  'unit' => 'set'],
                    ['name' => 'Lipstick Matte',             'cat' => 'Cosmetics & Beauty', 'price' => 350,  'cost' => 180,  'stock' => 80,  'unit' => 'pcs'],
                    ['name' => 'Foundation Stick',           'cat' => 'Cosmetics & Beauty', 'price' => 480,  'cost' => 270,  'stock' => 60,  'unit' => 'pcs'],
                    ['name' => 'Nail Polish Set (6pcs)',     'cat' => 'Cosmetics & Beauty', 'price' => 320,  'cost' => 160,  'stock' => 70,  'unit' => 'set'],
                ],
                'customers' => [
                    ['name' => 'Sharmin Akter',    'phone' => '01911500001', 'address' => 'Mohammadpur, Dhaka'],
                    ['name' => 'Mostofa Hossain',  'phone' => '01911500002', 'address' => 'Mohammadpur, Dhaka'],
                    ['name' => 'Kohinoor Begum',   'phone' => '01911500003', 'address' => 'Adabor, Dhaka'],
                    ['name' => 'Kawsar Ahmed',     'phone' => '01911500004', 'address' => 'Shyamoli, Dhaka'],
                    ['name' => 'Laila Akter',      'phone' => '01911500005', 'address' => 'Ring Road, Dhaka'],
                    ['name' => 'Babul Mia',        'phone' => '01911500006', 'address' => 'Mohammadpur, Dhaka'],
                    ['name' => 'Shirin Khanom',    'phone' => '01911500007', 'address' => 'Bosila, Dhaka'],
                    ['name' => 'Harun Rashid',     'phone' => '01911500008', 'address' => 'Rayer Bazar, Dhaka'],
                ],
                'suppliers' => [
                    ['name' => 'Stationery Wholesale',  'phone' => '01811500001', 'address' => 'Purana Paltan, Dhaka'],
                    ['name' => 'Home Decor Supply',     'phone' => '01811500002', 'address' => 'Chawkbazar, Dhaka'],
                    ['name' => 'Beauty Products BD',    'phone' => '01811500003', 'address' => 'New Market, Dhaka'],
                ],
            ],
        ];

        foreach ($branches as $branchDef) {
            $this->seedBranch($branchDef);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function seedBranch(array $def): void
    {
        // Vendor
        $vendor = Vendor::firstOrCreate(
            ['email' => $def['vendor']['email']],
            array_merge($def['vendor'], ['status' => 'active'])
        );
        $vid = $vendor->id;

        // Owner
        $owner = User::firstOrCreate(
            ['email' => $def['owner']['email']],
            ['name' => $def['owner']['name'], 'password' => Hash::make($def['owner']['password']), 'vendor_id' => $vid]
        );
        if (!$owner->hasRole('vendor-owner')) $owner->assignRole('vendor-owner');

        // Cashier
        $cashier = User::firstOrCreate(
            ['email' => $def['cashier']['email']],
            ['name' => $def['cashier']['name'], 'password' => Hash::make($def['cashier']['password']), 'vendor_id' => $vid]
        );
        if (!$cashier->hasRole('cashier')) $cashier->assignRole('cashier');

        // Categories
        $cats = [];
        foreach ($def['categories'] as $catName) {
            $cats[$catName] = Category::firstOrCreate(['vendor_id' => $vid, 'name' => $catName]);
        }

        // Suppliers
        $suppliers = [];
        foreach ($def['suppliers'] as $s) {
            $suppliers[] = Supplier::firstOrCreate(
                ['vendor_id' => $vid, 'phone' => $s['phone']],
                array_merge($s, ['vendor_id' => $vid, 'email' => null])
            );
        }

        // Products
        $products = [];
        foreach ($def['products'] as $p) {
            $product = Product::firstOrCreate(
                ['vendor_id' => $vid, 'name' => $p['name']],
                [
                    'category_id' => $cats[$p['cat']]->id,
                    'sku'         => 'SKU-' . strtoupper(Str::random(8)),
                    'price'       => $p['price'],
                    'cost_price'  => $p['cost'],
                    'stock_qty'   => $p['stock'],
                    'unit'        => $p['unit'],
                    'status'      => 'active',
                ]
            );
            $products[] = $product;
        }

        // Customers
        $customers = [];
        foreach ($def['customers'] as $c) {
            $customers[] = Customer::firstOrCreate(
                ['vendor_id' => $vid, 'phone' => $c['phone']],
                array_merge($c, ['vendor_id' => $vid, 'email' => null])
            );
        }

        // Purchases (last 30 days, every 3-4 days)
        if (Purchase::where('vendor_id', $vid)->count() === 0) {
            for ($d = 28; $d >= 0; $d -= rand(3, 5)) {
                $supplier = $suppliers[array_rand($suppliers)];
                $date     = Carbon::now()->subDays($d)->setHour(10)->setMinute(rand(0, 59));
                $selected = collect($products)->random(rand(2, 4));
                $subtotal = 0;
                $rows     = [];

                foreach ($selected as $prod) {
                    $qty  = rand(20, 80);
                    $cost = $prod->cost_price;
                    $sub  = $qty * $cost;
                    $subtotal += $sub;
                    $rows[] = compact('prod', 'qty', 'cost', 'sub');
                }

                $discount = rand(0, 1) ? rand(100, 500) : 0;
                $total    = max(0, $subtotal - $discount);
                $paid     = rand(0, 1) ? $total : round($total * 0.7, 2);
                $due      = max(0, $total - $paid);
                $pStatus  = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

                $purchase = Purchase::create([
                    'vendor_id'      => $vid,
                    'supplier_id'    => $supplier->id,
                    'created_by'     => $owner->id,
                    'invoice_no'     => 'PUR-' . strtoupper(Str::random(8)),
                    'subtotal'       => $subtotal,
                    'discount'       => $discount,
                    'total'          => $total,
                    'paid_amount'    => $paid,
                    'due_amount'     => $due,
                    'payment_status' => $pStatus,
                    'payment_method' => ['cash', 'bank_transfer', 'check'][rand(0, 2)],
                    'note'           => rand(0, 1) ? 'Regular stock replenishment' : null,
                    'created_at'     => $date,
                    'updated_at'     => $date,
                ]);

                foreach ($rows as $row) {
                    PurchaseItem::create([
                        'purchase_id'  => $purchase->id,
                        'product_id'   => $row['prod']->id,
                        'product_name' => $row['prod']->name,
                        'quantity'     => $row['qty'],
                        'unit_cost'    => $row['cost'],
                        'subtotal'     => $row['sub'],
                    ]);
                    StockMovement::create([
                        'vendor_id'      => $vid,
                        'product_id'     => $row['prod']->id,
                        'type'           => 'in',
                        'quantity'       => $row['qty'],
                        'reference_type' => 'purchase',
                        'reference_id'   => $purchase->id,
                        'note'           => 'Purchase: ' . $purchase->invoice_no,
                        'created_at'     => $date,
                    ]);
                }
            }
        }

        // Sales (last 30 days, 3-7 per day)
        if (Sale::where('vendor_id', $vid)->count() === 0) {
            $methods = ['cash', 'card', 'bank_transfer', 'other'];

            for ($day = 30; $day >= 0; $day--) {
                $salesPerDay = rand(3, 7);
                $date        = Carbon::now()->subDays($day);

                for ($s = 0; $s < $salesPerDay; $s++) {
                    $saleAt   = $date->copy()->setHour(rand(9, 21))->setMinute(rand(0, 59));
                    $customer = rand(0, 2) !== 0 ? $customers[array_rand($customers)] : null;
                    $method   = $methods[array_rand($methods)];
                    $discount = rand(0, 4) === 0 ? rand(20, 200) : 0;

                    $saleItems = collect($products)->random(rand(1, 3));
                    $subtotal  = 0;
                    $itemsData = [];

                    foreach ($saleItems as $prod) {
                        $qty   = rand(1, 3);
                        $price = $prod->price;
                        $itemsData[] = ['product' => $prod, 'qty' => $qty, 'price' => $price];
                        $subtotal += $qty * $price;
                    }

                    $total   = max(0, $subtotal - $discount);
                    $paid    = rand(0, 9) > 0 ? $total : round($total * 0.7, 2);
                    $due     = max(0, $total - $paid);
                    $pStatus = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

                    $sale = Sale::create([
                        'vendor_id'      => $vid,
                        'customer_id'    => $customer?->id,
                        'invoice_no'     => 'INV-' . strtoupper(Str::random(8)),
                        'subtotal'       => $subtotal,
                        'discount'       => $discount,
                        'tax'            => 0,
                        'total'          => $total,
                        'paid_amount'    => $paid,
                        'due_amount'     => $due,
                        'payment_status' => $pStatus,
                        'payment_method' => $method,
                        'order_status'   => 'completed',
                        'created_by'     => $cashier->id,
                        'created_at'     => $saleAt,
                        'updated_at'     => $saleAt,
                    ]);

                    foreach ($itemsData as $item) {
                        SaleItem::create([
                            'sale_id'    => $sale->id,
                            'product_id' => $item['product']->id,
                            'quantity'   => $item['qty'],
                            'unit_price' => $item['price'],
                            'subtotal'   => $item['qty'] * $item['price'],
                        ]);
                        StockMovement::create([
                            'vendor_id'      => $vid,
                            'product_id'     => $item['product']->id,
                            'type'           => 'out',
                            'quantity'       => $item['qty'],
                            'reference_type' => 'sale',
                            'reference_id'   => $sale->id,
                            'note'           => 'Sale: ' . $sale->invoice_no,
                            'created_at'     => $saleAt,
                        ]);
                    }

                    if ($paid > 0) {
                        Payment::create([
                            'vendor_id'  => $vid,
                            'sale_id'    => $sale->id,
                            'amount'     => $paid,
                            'method'     => $method,
                            'paid_at'    => $saleAt,
                            'created_at' => $saleAt,
                            'updated_at' => $saleAt,
                        ]);
                    }
                }
            }
        }

        // Income/Expense categories + 3 months of transactions
        $ieCategDefs = [
            ['name' => 'Shop Rent',        'type' => 'expense'],
            ['name' => 'Electricity Bill', 'type' => 'expense'],
            ['name' => 'Staff Salary',     'type' => 'expense'],
            ['name' => 'Marketing',        'type' => 'expense'],
            ['name' => 'Other Income',     'type' => 'income'],
        ];
        $ieCategs = [];
        foreach ($ieCategDefs as $c) {
            $ieCategs[$c['name']] = IncomeExpenseCategory::firstOrCreate(
                ['vendor_id' => $vid, 'name' => $c['name']],
                ['vendor_id' => $vid, 'name' => $c['name'], 'type' => $c['type'], 'color' => '#6b7280']
            );
        }

        if (IncomeExpense::where('vendor_id', $vid)->count() === 0) {
            $monthlyExpenses = [
                ['category' => 'Shop Rent',        'amount' => 18000],
                ['category' => 'Electricity Bill', 'amount' => 4000],
                ['category' => 'Staff Salary',     'amount' => 28000],
                ['category' => 'Marketing',        'amount' => 3000],
            ];

            for ($m = 2; $m >= 0; $m--) {
                $mDate = Carbon::now()->subMonths($m)->startOfMonth();
                foreach ($monthlyExpenses as $exp) {
                    IncomeExpense::create([
                        'vendor_id'   => $vid,
                        'category_id' => $ieCategs[$exp['category']]->id,
                        'created_by'  => $owner->id,
                        'type'        => 'expense',
                        'amount'      => $exp['amount'] + rand(-800, 800),
                        'date'        => $mDate->copy()->addDays(rand(1, 5))->toDateString(),
                        'note'        => $exp['category'] . ' - ' . $mDate->format('M Y'),
                        'reference'   => 'EXP-' . strtoupper(Str::random(6)),
                    ]);
                }
                for ($w = 0; $w < 4; $w++) {
                    IncomeExpense::create([
                        'vendor_id'   => $vid,
                        'category_id' => $ieCategs['Other Income']->id,
                        'created_by'  => $owner->id,
                        'type'        => 'income',
                        'amount'      => rand(800, 3000),
                        'date'        => $mDate->copy()->addWeeks($w)->addDays(rand(0, 6))->toDateString(),
                        'note'        => 'Additional income',
                        'reference'   => 'INC-' . strtoupper(Str::random(6)),
                    ]);
                }
            }
        }
    }
}
