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
        // Kiểm tra xem unique constraint đã tồn tại chưa
        // Constraint này đã được tạo trong migration create_bets_table
        // Nên migration này chỉ để đảm bảo tính nhất quán
        if (!$this->hasUniqueConstraint()) {
        Schema::table('bets', function (Blueprint $table) {
                // Add unique constraint: Mỗi user chỉ có thể đặt 1 bet trong 1 round
                $table->unique(['round_id', 'user_id'], 'bets_round_user_unique');
            });
        }
    }

    /**
     * Kiểm tra xem unique constraint đã tồn tại chưa
     */
    private function hasUniqueConstraint(): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        $constraint = $connection->selectOne(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.TABLE_CONSTRAINTS 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'bets' 
             AND CONSTRAINT_NAME = 'bets_round_user_unique' 
             AND CONSTRAINT_TYPE = 'UNIQUE'",
            [$database]
        );
        
        return $constraint !== null;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropUnique('bets_round_user_unique');
        });
    }
};
