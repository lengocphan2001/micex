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
        Schema::create('trading_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('symbol', 20)->index(); // BTCUSDT
            $table->enum('direction', ['up', 'down']); // Tăng hoặc Giảm
            $table->decimal('amount', 20, 8); // Số tiền cược
            $table->decimal('payout_rate', 10, 2)->default(1.95); // Tỉ lệ trả thưởng (95% = 1.95x)
            $table->decimal('entry_price', 20, 8); // Giá vào lệnh
            $table->decimal('exit_price', 20, 8)->nullable(); // Giá ra lệnh (khi kết thúc)
            $table->enum('status', ['pending', 'won', 'lost'])->default('pending');
            $table->decimal('profit', 20, 8)->default(0); // Lợi nhuận
            $table->bigInteger('round_time')->index(); // Timestamp của round (candle time)
            $table->timestamp('placed_at')->useCurrent();
            $table->timestamp('result_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['symbol', 'round_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trading_bets');
    }
};
