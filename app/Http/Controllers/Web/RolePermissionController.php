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
        'Dashboard' => ['view_dashboard'],
        'Sales'     => ['create_sales', 'view_sales', 'manage_payments'],
        'Products'  => ['manage_products', 'manage_categories', 'manage_inventory'],
        'People'    => ['manage_customers', 'manage_suppliers'],
        'Staff'     => ['manage_users'],
        'Reports'   => ['view_reports'],
    ];

    private array $labels = [
        'view_dashboard'    => ['View Dashboard', 'Access to the main dashboard page'],
        'create_sales'      => ['Create Sales / POS', 'Use the POS terminal to create new sales'],
        'view_sales'        => ['View Sales Records', 'Browse and view existing sale invoices'],
        'manage_payments'   => ['Process Payments', 'Add payments and change payment status on sales/purchases'],
        'manage_products'   => ['Manage Products', 'Add, edit, and delete products'],
        'manage_categories' => ['Manage Categories', 'Add, edit, and delete product categories'],
        'manage_inventory'  => ['Manage Inventory / Stock', 'Add stock adjustments and view stock movements'],
        'manage_customers'  => ['Manage Customers', 'Add, edit, and delete customer records'],
        'manage_suppliers'  => ['Manage Suppliers', 'Add, edit, and delete suppliers; manage purchases'],
        'manage_users'      => ['Manage Staff', 'Invite, edit, and remove branch staff members'],
        'view_reports'      => ['View Reports', 'Access sales, stock, and financial reports'],
    ];

    public function index()
    {
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
