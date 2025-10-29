<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if super admin already exists
        $existingSuperAdmin = User::where('role', User::ROLE_SUPER_ADMIN)->first();

        if ($existingSuperAdmin) {
            $this->command->warn('Super admin already exists!');
            $this->command->info('Email: ' . $existingSuperAdmin->email);
            return;
        }

        // Create super admin user
        $superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@manschoice.co.ke',
            'phone' => '0721237811',
            'password' => Hash::make('*#36964019hH'),
            'pin' => Hash::make('1234'),
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Super admin created successfully!');
        $this->command->info('Email: ' . $superAdmin->email);
        $this->command->info('Password: *#36964019hH');
        $this->command->info('PIN: 1234');
        $this->command->warn('Please change these credentials after first login!');
    }
}
