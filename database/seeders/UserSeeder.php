<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo thêm một vài user mẫu khác
        User::create([
            'phone_number' => '+84987654321',
            'display_name' => 'User Demo',
            'password' => Hash::make('password123'),
            'referral_code' => 'USER5678',
        ]);
    }
}
