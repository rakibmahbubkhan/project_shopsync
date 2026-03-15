<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    $admin = Role::create(['name' => 'admin']);
    $manager = Role::create(['name' => 'manager']);
    $cashier = Role::create(['name' => 'cashier']);

    $admin = Role::create(['name' => 'Admin']);
    $admin->permissions()->attach(Permission::all());

    $manager = Role::create(['name' => 'Manager']);
    $manager->permissions()->attach(Permission::whereIn('name', ['view-inventory', 'process-sales'])->get());

    $permissions = [
        'manage_products',
        'manage_categories',
        'manage_purchases',
        'manage_sales',
        'view_reports',
        'manage_users',
        'view-inventory',
        'edit-inventory', 
        'process-sales',
        
    ];

    foreach ($permissions as $perm) {
        $permission = Permission::create(['name' => $perm]);
        $admin->permissions()->attach($permission);
    }

    $manager->permissions()->attach(
        Permission::whereIn('name', [
            'manage_products',
            'manage_categories',
            'manage_purchases',
            'manage_sales',
            'view_reports'
        ])->pluck('id')
    );

    $cashier->permissions()->attach(
        Permission::whereIn('name', [
            'manage_sales'
        ])->pluck('id')
    );

    $permissions = [
        
    ];
}

}
