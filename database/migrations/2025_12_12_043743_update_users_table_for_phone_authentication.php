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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number', 20)->unique()->after('id');
            $table->string('display_name')->after('phone_number');
            $table->string('referral_code', 50)->nullable()->unique()->after('display_name');
            $table->foreignId('referred_by')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->string('email')->nullable()->change();
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['phone_number', 'display_name', 'referral_code', 'referred_by']);
            $table->string('email')->nullable(false)->change();
            $table->string('name')->nullable(false)->change();
        });
    }
};
