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
        Schema::create('candles', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20)->index(); // BTCUSDT
            $table->string('timeframe', 10)->index(); // 1m, 5m, 15m, 1h, etc.
            $table->bigInteger('time')->index(); // timestamp
            $table->decimal('open', 20, 8);
            $table->decimal('high', 20, 8);
            $table->decimal('low', 20, 8);
            $table->decimal('close', 20, 8);
            $table->decimal('volume', 20, 8)->default(0);
            $table->timestamps();
            
            // Unique constraint: same symbol + timeframe + time
            $table->unique(['symbol', 'timeframe', 'time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candles');
    }
};
