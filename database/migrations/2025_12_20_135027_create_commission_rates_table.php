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
        Schema::create('commission_rates', function (Blueprint $table) {
            $table->id();
            $table->string('level', 10); // F1, F2, F3, F4, F5, F6...
            $table->decimal('rate', 5, 2); // Tỉ lệ hoa hồng (ví dụ: 5.00 = 5%)
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0); // Thứ tự hiển thị
            $table->timestamps();
            
            $table->unique('level');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rates');
    }
};
