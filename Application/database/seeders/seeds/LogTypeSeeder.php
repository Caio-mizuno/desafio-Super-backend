<?php

namespace Database\Seeders\Seeds;

use Illuminate\Database\Seeder;
use App\Models\LogType;

class LogTypeSeeder extends Seeder
{
    public function run(): void
    {
        if (LogType::count() === 0) {
            LogType::create(['description' => 'PIX webhook processed']);
            LogType::create(['description' => 'Withdraw webhook processed']);
        }
    }
}

