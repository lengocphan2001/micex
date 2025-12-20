<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tìm hoặc tạo admin
        $admin = Admin::where('email', 'admin@micex.com')->first();

        if (!$admin) {
            // Create new admin
            Admin::create([
                'name' => 'Administrator',
                'email' => 'admin@micex.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]);
        }
    }
}
