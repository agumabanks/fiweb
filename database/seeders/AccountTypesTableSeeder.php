<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountType;

class AccountTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $accountTypes = [
            [
                'name' => 'Regular Savings',
                'description' => 'Basic savings account with standard interest rates.',
                'interest_rate' => 2.50,
                'compounding_frequency' => 'monthly',
                 
            ],
            [
                'name' => 'Premium Savings',
                'description' => 'Premium savings account with higher interest rates and additional benefits.',
                'interest_rate' => 3.50,
                'compounding_frequency' => 'monthly',
                 
            ],
            [
                'name' => 'Fixed Deposit',
                'description' => 'Fixed term deposit account with fixed interest rates.',
                'interest_rate' => 5.00,
                'compounding_frequency' => 'annually',
                 
            ],
            // Add more account types as needed
        ];

        foreach ($accountTypes as $type) {
            AccountType::create($type);
        }
    }
}
