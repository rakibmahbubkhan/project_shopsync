<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountsTableSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Asset accounts
            ['code' => '1001', 'name' => 'Cash', 'type' => 'asset'],
            ['code' => '1002', 'name' => 'Bank Account', 'type' => 'asset'],
            ['code' => '1101', 'name' => 'Accounts Receivable', 'type' => 'asset'],
            ['code' => '1201', 'name' => 'Inventory', 'type' => 'asset'],
            
            // Liability accounts
            ['code' => '2001', 'name' => 'Accounts Payable', 'type' => 'liability'],
            ['code' => '2101', 'name' => 'Sales Tax Payable', 'type' => 'liability'],
            
            // Equity accounts
            ['code' => '3001', 'name' => 'Owner\'s Equity', 'type' => 'equity'],
            ['code' => '3101', 'name' => 'Retained Earnings', 'type' => 'equity'],
            
            // Income accounts
            ['code' => '4001', 'name' => 'Sales Revenue', 'type' => 'income'],
            ['code' => '4101', 'name' => 'Service Revenue', 'type' => 'income'],
            
            // Expense accounts
            ['code' => '5001', 'name' => 'Cost of Goods Sold', 'type' => 'expense'],
            ['code' => '5101', 'name' => 'Rent Expense', 'type' => 'expense'],
            ['code' => '5201', 'name' => 'Utilities Expense', 'type' => 'expense'],
            ['code' => '5301', 'name' => 'Salaries Expense', 'type' => 'expense'],
        ];

        foreach ($accounts as $account) {
            Account::create($account);
        }
    }
}