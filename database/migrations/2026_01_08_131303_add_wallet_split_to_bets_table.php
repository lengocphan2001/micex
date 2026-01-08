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
            $table->decimal('amount_from_deposit', 15, 2)->default(0)->after('amount');
            $table->decimal('amount_from_reward', 15, 2)->default(0)->after('amount_from_deposit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropColumn(['amount_from_deposit', 'amount_from_reward']);
        });
    }
};
