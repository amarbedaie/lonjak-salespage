<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'salespage_id' => null,
            'customer' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '0123456789',
            'address' => fake()->address(),
            'state' => 'Selangor',
            'product_name' => 'Buku Tadabbur',
            'qty' => 1,
            'total' => 49.00,
            'status' => 'baru',
            'payment_status' => 'belum',
        ];
    }
}
