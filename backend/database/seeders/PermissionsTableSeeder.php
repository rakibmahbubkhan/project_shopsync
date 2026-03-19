<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User permissions
            ['name' => 'view_users', 'label' => 'View Users'],
            ['name' => 'create_users', 'label' => 'Create Users'],
            ['name' => 'edit_users', 'label' => 'Edit Users'],
            ['name' => 'delete_users', 'label' => 'Delete Users'],
            
            // Product permissions
            ['name' => 'view_products', 'label' => 'View Products'],
            ['name' => 'create_products', 'label' => 'Create Products'],
            ['name' => 'edit_products', 'label' => 'Edit Products'],
            ['name' => 'delete_products', 'label' => 'Delete Products'],
            
            // Sale permissions
            ['name' => 'view_sales', 'label' => 'View Sales'],
            ['name' => 'create_sales', 'label' => 'Create Sales'],
            ['name' => 'edit_sales', 'label' => 'Edit Sales'],
            ['name' => 'delete_sales', 'label' => 'Delete Sales'],
            ['name' => 'process_refunds', 'label' => 'Process Refunds'],
            
            // Purchase permissions
            ['name' => 'view_purchases', 'label' => 'View Purchases'],
            ['name' => 'create_purchases', 'label' => 'Create Purchases'],
            ['name' => 'edit_purchases', 'label' => 'Edit Purchases'],
            ['name' => 'delete_purchases', 'label' => 'Delete Purchases'],
            
            // Inventory permissions
            ['name' => 'view_inventory', 'label' => 'View Inventory'],
            ['name' => 'adjust_inventory', 'label' => 'Adjust Inventory'],
            ['name' => 'transfer_stock', 'label' => 'Transfer Stock'],
            
            // Report permissions
            ['name' => 'view_reports', 'label' => 'View Reports'],
            ['name' => 'export_reports', 'label' => 'Export Reports'],
            
            // Setting permissions
            ['name' => 'manage_settings', 'label' => 'Manage Settings'],
            ['name' => 'manage_roles', 'label' => 'Manage Roles'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}