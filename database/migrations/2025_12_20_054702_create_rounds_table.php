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
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('round_number')->unique(); // Kỳ số
            $table->enum('status', ['pending', 'running', 'finished'])->default('pending');
            $table->integer('current_second')->default(0); // Giây hiện tại (0-60)
            $table->string('current_result')->nullable(); // Kết quả random hiện tại
            $table->string('final_result')->nullable(); // Kết quả cuối cùng (giây 60)
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('round_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
