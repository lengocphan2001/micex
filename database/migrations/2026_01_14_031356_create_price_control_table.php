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
        Schema::create('price_control', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20)->unique(); // BTCUSDT
            $table->enum('mode', ['normal', 'up', 'down', 'trap'])->default('normal');
            $table->integer('strength')->default(1); // 1-10
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_control');
    }
};
