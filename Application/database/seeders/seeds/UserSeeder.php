<?php

namespace Database\Seeders\Seeds;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subacquirer;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $subadqAId = Subacquirer::where('name', 'SubadqA')->value('id');
        $subadqBId = Subacquirer::where('name', 'SubadqB')->value('id');

        if (!User::where('email', 'SubadqA@example.com')->exists()) {
            $user = User::create([
                'name' => 'User Login',
                'email' => 'SubadqA@example.com',
                'cpf_cnpj' => '48596938095',
                'password' => Hash::make('secret123'),
                'subacquirer_id' => $subadqAId,
            ]);
            $user->bankAccounts()->create([
                'bank_code' => '001',
                'branch' => '0001',
                'account_number' => '12345678900',
                'account_type' => 'checking',
            ]);
        }
        if (!User::where('email', 'SubadqB@example.com')->exists()) {
            $user = User::create([
                'name' => 'User Login',
                'email' => 'SubadqB@example.com',
                'cpf_cnpj' => '86807553030',
                'password' => Hash::make('secret123'),
                'subacquirer_id' => $subadqBId,
            ]);
            $user->bankAccounts()->create([
                'bank_code' => '001',
                'branch' => '0002',
                'account_number' => '98765432100',
                'account_type' => 'savings',
            ]);
        }
    }
}
