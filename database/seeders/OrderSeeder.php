<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $users->each(function ($user) {
            Order::factory()->count(3)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
