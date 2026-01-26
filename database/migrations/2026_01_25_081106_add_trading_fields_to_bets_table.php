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
            $table->enum('bet_direction', ['BUY', 'SELL'])->nullable()->after('gem_type');
            $table->decimal('matched_amount', 15, 2)->default(0)->after('amount');
            $table->decimal('pending_amount', 15, 2)->nullable()->after('matched_amount');
            $table->index(['round_id', 'bet_direction', 'status'], 'bets_trading_matching_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropIndex('bets_trading_matching_idx');
            $table->dropColumn(['bet_direction', 'matched_amount', 'pending_amount']);
        });
    }
};
