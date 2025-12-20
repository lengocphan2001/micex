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
        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('gem_amount', 15, 2); // Số lượng đá quý muốn rút
            $table->decimal('amount', 15, 2); // Số tiền VND tương ứng
            $table->string('bank_name')->nullable(); // Tên ngân hàng
            $table->string('bank_account')->nullable(); // Số tài khoản
            $table->string('bank_full_name')->nullable(); // Họ tên chủ tài khoản
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null'); // Admin who approved
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable(); // Ghi chú của admin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
    }
};
