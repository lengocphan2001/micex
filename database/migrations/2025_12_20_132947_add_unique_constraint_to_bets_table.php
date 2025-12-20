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
        Schema::table('bets', function (Blueprint $table) {
            // Add unique constraint: Mỗi user chỉ có thể đặt 1 bet trong 1 round
            $table->unique(['round_id', 'user_id'], 'bets_round_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropUnique('bets_round_user_unique');
        });
    }
};
