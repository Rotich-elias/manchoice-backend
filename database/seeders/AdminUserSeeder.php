<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        $adminExists = User::where('email', 'admin@manchoice.com')->exists();

        if ($adminExists) {
            $this->command->info('Admin user already exists.');
            return;
        }

        User::create([
            'name' => 'Admin',
            'email' => 'admin@manchoice.com',
            'password' => Hash::make('admin123'), // Change this password!
            'role' => 'admin',
            'phone' => '0700000000',
            'profile_completed' => false,
            'registration_fee_paid' => true,
            'registration_fee_amount' => 0,
            'registration_fee_paid_at' => now(),
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->warn('Email: admin@manchoice.com');
        $this->command->warn('Password: admin123 (CHANGE THIS!)');
    }
}
