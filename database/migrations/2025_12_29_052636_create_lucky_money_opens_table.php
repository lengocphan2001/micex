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
        Schema::create('lucky_money_opens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2); // Số đá quý nhận được
            $table->date('opened_date'); // Ngày mở (để kiểm tra daily limit)
            $table->timestamps();
            
            // Đảm bảo mỗi user chỉ mở 1 lần mỗi ngày
            $table->unique(['user_id', 'opened_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lucky_money_opens');
    }
};
