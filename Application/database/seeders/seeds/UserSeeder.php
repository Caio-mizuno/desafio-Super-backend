<?php

namespace Database\Seeders\Seeds;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', 'login@example.com')->exists()) {
            User::create([
                'name' => 'User Login',
                'email' => 'login@example.com',
                'password' => Hash::make('secret123'),
                'subacquirer' => 'SubadqA',
            ]);
        }
    }
}
