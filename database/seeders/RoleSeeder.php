<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['super-admin', 'vendor-owner', 'cashier', 'staff'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $permissions = [
            'view_dashboard',
            'manage_products',
            'manage_categories',
            'manage_inventory',
            'create_sales',
            'view_sales',
            'manage_customers',
            'manage_suppliers',
            'manage_users',
            'manage_payments',
            'view_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $vendorOwner = Role::where('name', 'vendor-owner')->first();
        $cashier = Role::where('name', 'cashier')->first();
        $staff = Role::where('name', 'staff')->first();

        $superAdmin  = Role::where('name', 'super-admin')->first();
        $superAdmin->syncPermissions($permissions);
        $vendorOwner->syncPermissions($permissions);
        $cashier->syncPermissions(['view_dashboard', 'create_sales', 'view_sales', 'manage_customers', 'view_reports']);
        $staff->syncPermissions(['view_dashboard', 'view_sales', 'view_reports']);
    }
}

