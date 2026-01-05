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
            // Drop the unique constraint that prevents multiple bets per user per round
            // This allows xanhdo game to have multiple bets per user (one per selection)
            $table->dropUnique('bets_round_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            // Restore the unique constraint
            $table->unique(['round_id', 'user_id'], 'bets_round_user_unique');
        });
    }
};
