<?php

namespace Database\Seeders\Seeds;

use Illuminate\Database\Seeder;
use App\Models\Subacquirer;

class SubacquirerSeeder extends Seeder
{
    public function run(): void
    {
        if (Subacquirer::count() === 0) {
            Subacquirer::create(['name' => 'SubadqA']);
            Subacquirer::create(['name' => 'SubadqB']);
        }
    }
}