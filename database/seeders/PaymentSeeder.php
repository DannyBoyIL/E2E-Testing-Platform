<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Only pay for completed orders
        Order::where('status', 'completed')->each(function ($order) {
            Payment::factory()->create([
                'user_id'  => $order->user_id,
                'order_id' => $order->id,
                'amount'   => $order->total,
            ]);
        });
    }
}
