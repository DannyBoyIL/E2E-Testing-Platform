<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'total'  => $this->faker->randomFloat(2, 10, 500),
            'notes'  => $this->faker->sentence(),
        ];
    }
}
