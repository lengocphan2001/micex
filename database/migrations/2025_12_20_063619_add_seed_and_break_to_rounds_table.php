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
        Schema::table('rounds', function (Blueprint $table) {
            $table->string('seed')->nullable()->after('round_number'); // Seed để client random đồng bộ
            $table->timestamp('break_until')->nullable()->after('ended_at'); // Thời gian break kết thúc
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->dropColumn(['seed', 'break_until']);
        });
    }
};
