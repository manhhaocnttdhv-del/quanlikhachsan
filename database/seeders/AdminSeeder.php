<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo admin mặc định
        Admin::create([
            'name' => 'Administrator',
            'email' => 'admin@hotel.com',
            'password' => Hash::make('admin123'),
            'phone' => '0123456789',
            'role' => 'admin',
        ]);

        // Tạo manager mẫu
        Admin::create([
            'name' => 'Manager',
            'email' => 'manager@hotel.com',
            'password' => Hash::make('manager123'),
            'phone' => '0987654321',
            'role' => 'manager',
        ]);
    }
}
