<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siteAccount = Account::create([
            'id'=>1,
            'account_number'=>"1111111111111111",
            'balance' => 0
        ]);
    }
}
