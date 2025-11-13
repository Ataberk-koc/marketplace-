<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin kullanıcısı
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Test satıcı
        User::create([
            'name' => 'Test Seller',
            'email' => 'seller@test.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'is_active' => true,
        ]);

        $this->command->info('✅ Admin: admin@admin.com / password');
        $this->command->info('✅ Seller: seller@test.com / password');
    }
}
