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
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('gem_type', ['thachanh', 'thachanhtim', 'ngusac', 'daquy', 'cuoc', 'kimcuong']);
            $table->decimal('amount', 15, 2); // Số đá quý đặt cược
            $table->decimal('payout_rate', 8, 2); // Tỉ lệ ăn
            $table->enum('status', ['pending', 'won', 'lost'])->default('pending');
            $table->decimal('payout_amount', 15, 2)->nullable(); // Số tiền nhận được nếu thắng
            $table->timestamps();
            
            $table->index(['round_id', 'user_id']);
            $table->index('status');
            
            // Unique constraint: Mỗi user chỉ có thể đặt 1 bet trong 1 round
            $table->unique(['round_id', 'user_id'], 'bets_round_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};
