<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'vnd_to_gem_rate'
            $table->text('value'); // e.g., '1000' (1 VND = 0.001 gem, or 1000 VND = 1 gem)
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Insert default rate: 1000 VND = 1 gem
        DB::table('system_settings')->insert([
            'key' => 'vnd_to_gem_rate',
            'value' => '1000',
            'description' => 'Tỉ lệ quy đổi: số VND cần để đổi được 1 đá quý',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
