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
        Schema::create('giftcodes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // Mã giftcode
            $table->integer('quantity')->default(1); // Số lượng giftcode có thể sử dụng
            $table->integer('used_count')->default(0); // Số lượng đã sử dụng
            $table->decimal('value', 15, 2); // Giá trị giftcode (số tiền/đá quý)
            $table->date('expires_at')->nullable(); // Thời hạn
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        Schema::create('giftcode_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('giftcode_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2); // Số tiền đã nhận
            $table->timestamps();
            
            // Đảm bảo mỗi user chỉ dùng 1 giftcode 1 lần
            $table->unique(['giftcode_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giftcode_usages');
        Schema::dropIfExists('giftcodes');
    }
};
