<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate admin users from users table to admins table
        $adminUsers = \DB::table('users')
            ->where('role', 'admin')
            ->get();

        foreach ($adminUsers as $user) {
            // Check if admin already exists
            $existingAdmin = \DB::table('admins')
                ->where('email', $user->email)
                ->first();

            if (!$existingAdmin) {
                \DB::table('admins')->insert([
                    'name' => $user->name ?? $user->display_name ?? 'Admin',
                    'email' => $user->email,
                    'password' => $user->password,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, you can reverse this migration
        // For safety, we'll leave admins table as is
    }
};
