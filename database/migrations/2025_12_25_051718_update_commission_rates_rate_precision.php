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
        Schema::table('commission_rates', function (Blueprint $table) {
            // Change rate column to allow more decimal places (up to 8 decimal places)
            $table->decimal('rate', 10, 8)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_rates', function (Blueprint $table) {
            // Revert back to 2 decimal places
            $table->decimal('rate', 5, 2)->change();
        });
    }
};
