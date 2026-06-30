<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// Idempotent — creates the owner admin account on first deploy. Safe to re-run.
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'amarmirza94@gmail.com'],
            [
                'business_name' => 'Mendap',
                'name' => 'Admin',
                'password' => 'lonjak2026',
                'role' => 'admin',
                'plan' => 'pro',
                'ai_credits' => 999,
            ],
        );
    }
}
