<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_full_name')->nullable();
            $table->string('fund_password')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account', 'bank_full_name', 'fund_password']);
        });
    }
};

