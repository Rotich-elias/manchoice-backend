<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete ALL users
        User::truncate();

        // Create ONE fresh admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@manchoice.com',
            'phone' => '254700000000',
            'password' => bcrypt('password'),
            'pin' => bcrypt('1234'),
            'role' => 'admin',
            'profile_completed' => true,
            'registration_fee_paid' => true,
            'registration_fee_amount' => 0,
            'registration_fee_paid_at' => now(),
        ]);
    }
}
