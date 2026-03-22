<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'amount'         => $this->faker->randomFloat(2, 10, 500),
            'status'         => 'successful',
            'payment_method' => $this->faker->randomElement(['credit_card', 'debit_card', 'paypal', 'bank_transfer']),
            'transaction_id' => Str::uuid(),
        ];
    }
}
