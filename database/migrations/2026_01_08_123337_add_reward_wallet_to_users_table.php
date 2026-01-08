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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('reward_balance', 15, 2)->default(0)->after('balance');
            $table->datetime('last_reward_at')->nullable()->after('reward_balance');
            $table->datetime('last_bet_from_reward_at')->nullable()->after('last_reward_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reward_balance', 'last_reward_at', 'last_bet_from_reward_at']);
        });
    }
};
