<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionTableSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $cashierRole = Role::where('name', 'cashier')->first();
        
        // Assign all permissions to admin
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));
        
        // Assign specific permissions to manager
        $managerPermissions = Permission::whereIn('name', [
            'view_products', 'create_products', 'edit_products',
            'view_sales', 'create_sales', 'edit_sales',
            'view_purchases', 'create_purchases',
            'view_inventory', 'adjust_inventory',
            'view_reports', 'export_reports',
        ])->get();
        $managerRole->permissions()->sync($managerPermissions->pluck('id'));
        
        // Assign limited permissions to cashier
        $cashierPermissions = Permission::whereIn('name', [
            'view_products',
            'view_sales', 'create_sales',
            'view_inventory',
        ])->get();
        $cashierRole->permissions()->sync($cashierPermissions->pluck('id'));
    }
}