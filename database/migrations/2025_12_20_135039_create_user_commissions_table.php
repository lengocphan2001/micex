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
        Schema::create('user_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User nhận hoa hồng
            $table->foreignId('from_user_id')->constrained('users')->onDelete('cascade'); // User tạo ra hoa hồng (bet và thắng)
            $table->foreignId('bet_id')->nullable()->constrained()->onDelete('set null'); // Bet tạo ra hoa hồng
            $table->string('level', 10); // F1, F2, F3...
            $table->decimal('bet_amount', 15, 2); // Số tiền bet của from_user
            $table->decimal('commission_rate', 5, 2); // Tỉ lệ hoa hồng tại thời điểm đó
            $table->decimal('commission_amount', 15, 2); // Số tiền hoa hồng
            $table->enum('status', ['pending', 'available', 'withdrawn'])->default('available'); // pending: chưa xử lý, available: có thể rút, withdrawn: đã rút
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['from_user_id', 'bet_id']);
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_commissions');
    }
};
