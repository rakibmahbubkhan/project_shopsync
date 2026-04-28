<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Master data first
            RolesTableSeeder::class,
            PermissionsTableSeeder::class,
            
            // User related
            UsersTableSeeder::class,
            RolePermissionTableSeeder::class,
            
            
            // Core data
            CategoriesTableSeeder::class,
            BrandsTableSeeder::class,
            UnitsTableSeeder::class,
            
            // Parties
            CustomersTableSeeder::class,
            SuppliersTableSeeder::class,
            
            // Inventory locations
            WarehousesTableSeeder::class,
            
            
            // Products
            ProductsTableSeeder::class,

            ProductWarehouseTableSeeder::class,
            
            // Accounting
            AccountsTableSeeder::class,
            
            // Transactions (order matters due to dependencies)
            PurchasesTableSeeder::class,
            SalesTableSeeder::class,
            SaleItemsTableSeeder::class,
            SaleReturnSeeder::class,        
            SaleReturnDemoSeeder::class,
            ProductStocksTableSeeder::class,
        ]);
    }
}