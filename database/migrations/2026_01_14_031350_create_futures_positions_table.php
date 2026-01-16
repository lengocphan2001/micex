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
        Schema::create('futures_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('symbol', 20)->index(); // BTCUSDT
            $table->enum('side', ['long', 'short']); // long or short
            $table->decimal('entry_price', 20, 8);
            $table->decimal('size', 20, 8); // position size
            $table->integer('leverage')->default(1); // leverage multiplier
            $table->decimal('margin', 20, 8); // margin used
            $table->decimal('pnl', 20, 8)->default(0); // current PnL
            $table->enum('status', ['open', 'closed', 'liquidated'])->default('open');
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['symbol', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('futures_positions');
    }
};
