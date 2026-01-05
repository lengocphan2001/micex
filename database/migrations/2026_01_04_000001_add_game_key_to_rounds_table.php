<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            // Default existing rounds to "khaithac" so current game keeps working.
            $table->string('game_key', 32)->default('khaithac')->after('id');
        });

        // round_number was globally unique; we need it unique per game.
        Schema::table('rounds', function (Blueprint $table) {
            // Default Laravel name for the old unique index:
            // rounds_round_number_unique
            $table->dropUnique('rounds_round_number_unique');
            $table->unique(['game_key', 'round_number'], 'rounds_game_round_unique');
            $table->index(['game_key', 'status'], 'rounds_game_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->dropIndex('rounds_game_status_idx');
            $table->dropUnique('rounds_game_round_unique');
            $table->unique('round_number', 'rounds_round_number_unique');
            $table->dropColumn('game_key');
        });
    }
};


