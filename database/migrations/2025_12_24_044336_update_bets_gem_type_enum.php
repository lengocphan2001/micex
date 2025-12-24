<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing data: thachanh -> kcxanh, kimcuong -> kcdo
        DB::table('bets')
            ->where('gem_type', 'thachanh')
            ->update(['gem_type' => 'kcxanh']);
            
        DB::table('bets')
            ->where('gem_type', 'kimcuong')
            ->update(['gem_type' => 'kcdo']);

        // Update enum column
        DB::statement("ALTER TABLE `bets` MODIFY COLUMN `gem_type` ENUM('kcxanh', 'thachanhtim', 'ngusac', 'daquy', 'cuoc', 'kcdo') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data: kcxanh -> thachanh, kcdo -> kimcuong
        DB::table('bets')
            ->where('gem_type', 'kcxanh')
            ->update(['gem_type' => 'thachanh']);
            
        DB::table('bets')
            ->where('gem_type', 'kcdo')
            ->update(['gem_type' => 'kimcuong']);

        // Revert enum column
        DB::statement("ALTER TABLE `bets` MODIFY COLUMN `gem_type` ENUM('thachanh', 'thachanhtim', 'ngusac', 'daquy', 'cuoc', 'kimcuong') NOT NULL");
    }
};
