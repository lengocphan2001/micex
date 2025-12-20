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
        Schema::create('deposit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2); // Số tiền nạp (VND)
            $table->decimal('gem_amount', 15, 2)->nullable(); // Số lượng đá quý tương ứng
            $table->string('transfer_code')->nullable(); // Mã chuyển tiền
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Admin who approved
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
        Schema::dropIfExists('deposit_requests');
    }
};
