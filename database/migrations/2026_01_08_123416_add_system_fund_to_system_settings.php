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
        // Insert system_fund setting if it doesn't exist
        $exists = \Illuminate\Support\Facades\DB::table('system_settings')
            ->where('key', 'system_fund')
            ->exists();
        
        if (!$exists) {
            \Illuminate\Support\Facades\DB::table('system_settings')->insert([
                'key' => 'system_fund',
                'value' => '1000',
                'description' => 'Tổng quỹ hệ thống (đá quý)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('system_settings')
            ->where('key', 'system_fund')
            ->delete();
    }
};
