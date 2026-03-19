<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'label' => 'Administrator'],
            ['name' => 'manager', 'label' => 'Manager'],
            ['name' => 'cashier', 'label' => 'Cashier'],
            ['name' => 'staff', 'label' => 'Staff'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}