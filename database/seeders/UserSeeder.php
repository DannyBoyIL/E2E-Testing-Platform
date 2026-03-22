<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create a fixed admin user for easy login
        User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);

        // Create 9 random users
        User::factory()->count(9)->create();
    }
}
