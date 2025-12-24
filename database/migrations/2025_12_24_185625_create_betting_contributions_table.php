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
        Schema::create('betting_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('giftcode_usage_id')->nullable()->constrained('giftcode_usages', 'id')->onDelete('cascade');
            $table->decimal('amount', 15, 2); // Số đá quý đóng góp vào betting requirement
            $table->string('source')->default('giftcode'); // Nguồn: giftcode, deposit, etc.
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('betting_contributions');
    }
};
