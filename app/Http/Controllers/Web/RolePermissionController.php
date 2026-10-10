<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    private array $groups = [
        'Dashboard & Sales' => [
            'view_dashboard',
            'create_sales',
            'view_sales',
            'manage_payments',
            'view_sales_report',
        ],
        'Products & Catalog' => [
            'manage_products',
            'manage_categories',
            'manage_inventory',
            'view_stock_report',
            'manage_stock_transfer',
        ],
        'Purchases & Suppliers' => [
            'manage_purchases',
            'manage_suppliers',
        ],
        'Customers' => [
            'manage_customers',
        ],
        'Reports & Analytics' => [
            'view_reports',
            'view_financial_report',
            'manage_ads_cost',
            'manage_fraud_check',
        ],
        'Reseller Hub' => [
            'manage_resellers',
            'manage_reseller_orders',
            'manage_reseller_withdrawals',
            'view_reseller_reports',
        ],
        'Supplier Hub' => [
            'manage_suppliers_hub',
            'manage_supplier_products',
            'manage_supplier_sales',
            'manage_supplier_withdrawals',
        ],
        'Live Chat & Support' => [
            'manage_messages',
        ],
        'Staff & Moderation' => [
            'manage_users',
            'manage_moderators',
            'manage_roles_permissions',
        ],
        'Storefront & CMS' => [
            'manage_banners',
            'manage_home_contents',
            'manage_pages',
            'manage_flash_sales',
            'manage_coupons',
        ],
        'Settings & Logistics' => [
            'manage_order_statuses',
            'manage_payment_methods',
            'manage_shipping_charges',
            'manage_couriers',
            'manage_settings',
            'view_activity_logs',
            'view_user_activity',
        ],
    ];

    private array $labels = [
        // Dashboard & Sales
        'view_dashboard'              => ['View Dashboard', 'Access to overview and main dashboard statistics'],
        'create_sales'                => ['Create Sales / POS', 'Use the POS terminal to process direct sales'],
        'view_sales'                  => ['View Sales Records', 'Browse and view all sale invoices & orders across branches'],
        'manage_payments'             => ['Process Payments', 'Add payments, edit invoices, and update order payment status'],
        'view_sales_report'           => ['Order & Sales Report', 'View comprehensive order calculations, returns & shipping deductions'],

        // Products & Catalog
        'manage_products'             => ['Manage Products', 'Add, edit, generate barcodes, and manage catalog products'],
        'manage_categories'           => ['Manage Categories', 'Add, edit, and delete product categories and subcategories'],
        'manage_inventory'            => ['Manage Stock / Inventory', 'Adjust stock quantities and inspect stock movements'],
        'view_stock_report'           => ['Stock Report', 'Access warehouse & branch stock valuation and low-stock alerts'],
        'manage_stock_transfer'       => ['Stock Transfer', 'Transfer stock between warehouse and retail branches'],

        // Purchases & Suppliers
        'manage_purchases'            => ['All Purchases', 'Manage procurement, purchase orders, and purchase receipts'],
        'manage_suppliers'            => ['Purchase Suppliers', 'Add, edit, and manage raw goods & purchase suppliers'],

        // Customers
        'manage_customers'            => ['Manage Customers', 'View, add, edit, and manage registered retail customers'],

        // Reports & Analytics
        'view_reports'                => ['General Reports', 'Access branch performance, operational analytics and summaries'],
        'view_financial_report'       => ['Financial Report', 'Access income, expenses, profit statements and balance sheets'],
        'manage_ads_cost'             => ['Ads Cost Management', 'Track, add, export and analyze digital advertising expenses'],
        'manage_fraud_check'          => ['Fraud Check', 'Verify customer delivery success history and fraud risk scores'],

        // Reseller Hub
        'manage_resellers'            => ['All Resellers', 'Review, approve, reject, edit and manage reseller accounts'],
        'manage_reseller_orders'      => ['Reseller Orders', 'Track and manage orders dispatched on behalf of resellers'],
        'manage_reseller_withdrawals' => ['Reseller Withdrawals', 'Review, approve, reject and process reseller wallet payouts'],
        'view_reseller_reports'       => ['Reseller Reports', 'Analyze reseller sales volume, earnings and profit performance'],

        // Supplier Hub
        'manage_suppliers_hub'        => ['Suppliers / Vendors', 'Approve, verify, edit and manage supplier marketplace vendors'],
        'manage_supplier_products'    => ['Supplier Products Approval', 'Review, approve, reject and set commission for supplier items'],
        'manage_supplier_sales'       => ['Supplier Sales', 'View and manage marketplace orders containing supplier goods'],
        'manage_supplier_withdrawals' => ['Supplier Withdrawals', 'Review, approve and process supplier earnings payouts'],

        // Live Chat & Support
        'manage_messages'             => ['Live Chat / Messages', 'Reply to customer real-time chat inquiries and messages'],

        // Staff & Moderation
        'manage_users'                => ['All Staff Members', 'Add, edit, invite and manage branch staff accounts and roles'],
        'manage_moderators'           => ['Moderators & Work', 'Manage call-center moderators, verify work logs and withdrawals'],
        'manage_roles_permissions'    => ['Roles & Permissions', 'Manage role definitions and assign granular permission access'],

        // Storefront & CMS
        'manage_banners'              => ['Homepage Banners', 'Upload and configure promotional sliders and promo cards'],
        'manage_home_contents'        => ['Homepage Content', 'Organize storefront product shelves and category sections'],
        'manage_pages'                => ['Dynamic Pages', 'Create and edit custom customer service pages and policies'],
        'manage_flash_sales'          => ['Flash Sales', 'Create flash sale campaigns and limited-time discount deals'],
        'manage_coupons'              => ['Coupons & Discounts', 'Create and manage discount promo codes and validity dates'],

        // Settings & Logistics
        'manage_order_statuses'       => ['Order Statuses', 'Customize order lifecycle statuses, colors and order progression'],
        'manage_payment_methods'      => ['Payment Methods', 'Manage accepted payment gateways and payment options'],
        'manage_shipping_charges'     => ['Shipping Charges', 'Set delivery rates for inside/outside Dhaka districts'],
        'manage_couriers'             => ['Courier Settings', 'Configure Steadfast, Pathao, RedX API credentials and webhook'],
        'manage_settings'             => ['System Settings', 'Configure store identity, logo, currency, and global settings'],
        'view_activity_logs'          => ['Activity Logs', 'View audit trail of system modifications and staff actions'],
        'view_user_activity'          => ['User Traffic Analytics', 'Monitor real-time website visitors, page hits and traffic logs'],
    ];

    public function index()
    {
        // Ensure all defined permissions exist in the database table
        $allPermNames = collect($this->groups)->flatten()->unique();
        $existing = Permission::pluck('name')->flip();

        $missing = $allPermNames->reject(fn($name) => isset($existing[$name]));
        if ($missing->isNotEmpty()) {
            foreach ($missing as $permName) {
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            }
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $roles       = Role::whereNotIn('name', ['super-admin'])->orderBy('id')->get();
        $superAdmin  = Role::where('name', 'super-admin')->first();
        $permissions = Permission::all()->keyBy('name');

        // Build role → permission name set for quick lookup
        $rolePerms = [];
        foreach ($roles as $role) {
            $rolePerms[$role->id] = $role->permissions->pluck('name')->flip()->toArray();
        }

        return view('admin.roles-permissions', [
            'roles'      => $roles,
            'superAdmin' => $superAdmin,
            'permissions'=> $permissions,
            'groups'     => $this->groups,
            'labels'     => $this->labels,
            'rolePerms'  => $rolePerms,
        ]);
    }

    public function update(Request $request)
    {
        $roles = Role::whereNotIn('name', ['super-admin'])->get();

        foreach ($roles as $role) {
            $perms = $request->input("roles.{$role->id}", []);
            $role->syncPermissions($perms);
        }

        // Bust spatie cache so changes take effect immediately
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'Permissions updated successfully.');
    }
}
