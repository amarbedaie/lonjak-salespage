<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Salespage;
use App\Models\User;
use App\Services\SalespageGenerator;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $gen = app(SalespageGenerator::class);

        $admin = User::create([
            'business_name' => 'Kedai Demo', 'name' => 'Admin Demo', 'phone' => '012-3456789',
            'email' => 'admin@lonjak.my', 'password' => 'password',
            'role' => 'admin', 'plan' => 'pro', 'ai_credits' => 3,
        ]);

        User::create([
            'business_name' => 'Glow Empire', 'name' => 'Glow Empire', 'phone' => '011-22334455',
            'email' => 'glow@empire.my', 'password' => 'password',
            'role' => 'merchant', 'plan' => 'pro', 'ai_credits' => 1,
        ]);

        $brief = [
            'name' => 'Serum Glow Booster', 'price' => 89, 'comparePrice' => 159,
            'category' => 'Kecantikan', 'audience' => 'wanita 25–40 kulit kusam',
            'problem' => 'kulit kusam, jeragat & tak sekata',
            'benefits' => 'hasil 7 hari, bahan semula jadi, sesuai semua kulit', 'tone' => 'santai',
        ];

        $sp = Salespage::create([
            'user_id' => $admin->id, 'title' => 'Serum Glow Booster', 'slug' => 'serum-glow-demo',
            'product_name' => 'Serum Glow Booster', 'price' => 89, 'compare_price' => 159,
            'category' => 'Kecantikan', 'status' => 'live', 'gateway' => 'BayarCash',
            'brief' => $brief, 'blocks' => $gen->mock($brief), 'visits' => 842,
        ]);

        Salespage::create([
            'user_id' => $admin->id, 'title' => 'Set Kurus 14 Hari', 'slug' => 'kurus-14hari-demo',
            'product_name' => 'Pakej Detox 14 Hari', 'price' => 180, 'compare_price' => 280,
            'category' => 'Kesihatan', 'status' => 'draf', 'visits' => 0,
        ]);

        Product::create([
            'user_id' => $admin->id, 'name' => 'Serum Glow Booster 30ml', 'sku' => 'SGB-030',
            'price' => 89, 'cost' => 28, 'stock' => 120, 'sold' => 64, 'image' => '🧴',
        ]);

        $orders = [
            ['Nurul Aina', '013-3349281', 'Selangor', 1, 89, 'baru', 25],
            ['Ahmad Faiz', '012-7741022', 'Johor', 2, 178, 'dihantar', 320],
            ['Siti Khadijah', '019-6627710', 'Pulau Pinang', 1, 89, 'selesai', 1500],
        ];
        foreach ($orders as [$cust, $phone, $state, $qty, $total, $status, $mins]) {
            Order::create([
                'user_id' => $admin->id, 'salespage_id' => $sp->id, 'customer' => $cust, 'phone' => $phone,
                'address' => 'Alamat penghantaran, Malaysia', 'state' => $state, 'product_name' => 'Serum Glow Booster',
                'qty' => $qty, 'total' => $total, 'status' => $status,
                'payment_status' => $status === 'selesai' ? 'dibayar' : 'belum',
                'created_at' => now()->subMinutes($mins), 'updated_at' => now()->subMinutes($mins),
            ]);
        }
    }
}
