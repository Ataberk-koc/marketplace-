<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin kullanıcı
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@marketplace.com',
            'phone' => '5551234567',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Satıcı kullanıcılar
        User::create([
            'name' => 'Satıcı 1',
            'email' => 'seller1@marketplace.com',
            'phone' => '5551234568',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Satıcı 2',
            'email' => 'seller2@marketplace.com',
            'phone' => '5551234569',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Müşteri kullanıcılar
        User::create([
            'name' => 'Müşteri 1',
            'email' => 'customer1@marketplace.com',
            'phone' => '5551234570',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Müşteri 2',
            'email' => 'customer2@marketplace.com',
            'phone' => '5551234571',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Ek test kullanıcıları
        User::factory(10)->create();
    }
}
