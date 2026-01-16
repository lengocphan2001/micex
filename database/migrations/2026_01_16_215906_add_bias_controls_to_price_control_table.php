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
        Schema::table('price_control', function (Blueprint $table) {
            // bias_dir: 0 = tự nhiên, 1 = ép lên, -1 = ép xuống
            $table->integer('bias_dir')->default(0)->after('strength');
            // last_seconds: số giây cuối để ép (1-60, mặc định 10)
            $table->integer('last_seconds')->default(10)->after('bias_dir');
            // bias_power: độ lệch giá để ép (0-100, mặc định 10)
            $table->integer('bias_power')->default(10)->after('last_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_control', function (Blueprint $table) {
            $table->dropColumn(['bias_dir', 'last_seconds', 'bias_power']);
        });
    }
};
