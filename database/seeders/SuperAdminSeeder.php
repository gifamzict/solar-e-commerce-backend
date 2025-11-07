<?php

namespace Database\Seeders;

use App\Models\Admin;
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
        // Check if superadmin already exists
        $existingAdmin = Admin::where('email', 'admin@gifamz.com')->first();
        
        if ($existingAdmin) {
            $this->command->info('Super admin already exists!');
            return;
        }

        // Create superadmin account
        Admin::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@gifamz.com',
            'phone_number' => '+2348000000000',
            'role' => 'superadmin',
            'permissions' => [
                'manage_products',
                'manage_orders',
                'manage_users',
                'manage_admins',
                'manage_settings',
                'view_analytics',
                'manage_promotions',
                'manage_categories',
            ],
            'password' => Hash::make('Admin@123'),
            'status' => 'active',
        ]);

        $this->command->info('Super admin created successfully!');
        $this->command->info('Email: admin@gifamz.com');
        $this->command->info('Password: Admin@123');
        $this->command->warn('Please change the password after first login!');
    }
}
