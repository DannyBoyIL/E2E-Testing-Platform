<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create a fixed admin user — credentials come from .env (TEST_USER_EMAIL / TEST_USER_PASSWORD)
        User::factory()->create([
            'name' => 'Admin User',
            'email' => env('TEST_USER_EMAIL')
                ?? throw new \RuntimeException('TEST_USER_EMAIL is not set. Add it to your .env file.'),
            'password' => bcrypt(
                env('TEST_USER_PASSWORD')
                ?? throw new \RuntimeException('TEST_USER_PASSWORD is not set. Add it to your .env file.')
            ),
        ]);

        // Create 9 random users
        User::factory()->count(9)->create();
    }
}
