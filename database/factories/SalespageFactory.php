<?php

namespace Database\Factories;

use App\Models\Salespage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Salespage>
 */
class SalespageFactory extends Factory
{
    protected $model = Salespage::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'product_name' => 'Buku Tadabbur',
            'price' => 49.00,
            'status' => 'live',
            'gateway' => 'BayarCash',
            'provision_system' => null,
        ];
    }
}
