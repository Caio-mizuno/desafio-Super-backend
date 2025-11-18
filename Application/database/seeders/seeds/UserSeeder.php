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
            User::create([
                'name' => 'User Login',
                'email' => 'SubadqA@example.com',
                'cpf_cnpj' => '48596938095',
                'password' => Hash::make('secret123'),
                'subacquirer_id' => $subadqAId,
            ]);
        }
        if (!User::where('email', 'SubadqB@example.com')->exists()) {
            User::create([
                'name' => 'User Login',
                'email' => 'SubadqB@example.com',
                'cpf_cnpj' => '86807553030',
                'password' => Hash::make('secret123'),
                'subacquirer_id' => $subadqBId,
            ]);
        }
    }
}
