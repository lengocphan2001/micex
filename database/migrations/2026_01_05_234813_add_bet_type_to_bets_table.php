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
            $table->enum('bet_type', ['number', 'color'])->nullable()->after('gem_type');
            $table->string('bet_value', 10)->nullable()->after('bet_type'); // For number bets: '0'-'9', for color bets: null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropColumn(['bet_type', 'bet_value']);
        });
    }
};
