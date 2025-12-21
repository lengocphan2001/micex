<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'round_number',
        'seed',
        'status',
        'current_second',
        'current_result',
        'final_result',
        'admin_set_result',
        'results',
        'started_at',
        'ended_at',
        'break_until',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'break_until' => 'datetime',
        'results' => 'array', // JSON array of results
    ];

    /**
     * Relationship: Bets
     */
    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    /**
     * Base time để tính round number (phải giống client và ProcessRoundTimer)
     */
    const BASE_TIME = '2025-01-01 00:00:00';
    
    /**
     * Calculate round number based on BASE_TIME
     * Round duration: 60 giây
     */
    public static function calculateRoundNumber()
    {
        $baseTime = \Carbon\Carbon::parse(self::BASE_TIME)->timestamp;
        $now = now()->timestamp;
        $elapsed = $now - $baseTime;
        $totalCycle = 60; // 60 giây mỗi cycle
        return floor($elapsed / $totalCycle) + 1;
    }
    
    /**
     * Get current active round WITHOUT creating new one
     * Round should only be created by ProcessRoundTimer command
     */
    public static function getCurrentRound()
    {
        // Tính round number từ BASE_TIME
        $roundNumber = self::calculateRoundNumber();
        $expectedSeed = 'round_' . $roundNumber;
        
        // CHỈ lấy round có seed deterministic (round_ + roundNumber), BỎ round có seed random
        return self::where('round_number', $roundNumber)
            ->where('seed', $expectedSeed)
            ->first();
    }
    
    /**
     * Get current active round or create new one
     * @deprecated Use getOrCreateRoundByNumber() instead
     * NOTE: This method is deprecated and should NOT be used anymore.
     * Round creation should only happen in ProcessRoundTimer command.
     */
    public static function getCurrentRoundOrCreate()
    {
        // Tính round number từ BASE_TIME
        $roundNumber = self::calculateRoundNumber();
        
        return self::getOrCreateRoundByNumber($roundNumber);
    }
    
    /**
     * Get or create round by round number
     * Tự động tạo round nếu chưa có, đảm bảo chạy background
     * Sử dụng lock để tránh race condition khi tạo round
     */
    public static function getOrCreateRoundByNumber($roundNumber)
    {
        // Sử dụng lock để tránh race condition khi nhiều process cùng tạo round
        return \DB::transaction(function () use ($roundNumber) {
            $expectedSeed = 'round_' . $roundNumber;
            
            // CHỈ tìm round có seed deterministic (round_ + roundNumber)
            // BỎ TẤT CẢ round có seed random
            $round = self::where('round_number', $roundNumber)
                ->where('seed', $expectedSeed)
                ->lockForUpdate()
                ->first();
            
            if (!$round) {
                // Double-check sau khi lock để tránh race condition
                $round = self::where('round_number', $roundNumber)
                    ->where('seed', $expectedSeed)
                    ->first();
                
                if (!$round) {
                    // Generate deterministic seed for this round (phải giống client)
                    // Client dùng: 'round_' + roundNumber
                    $seed = 'round_' . $roundNumber;
                    
                    try {
            $round = self::create([
                            'round_number' => $roundNumber,
                'seed' => $seed,
                'status' => 'pending',
                'current_second' => 0,
            ]);
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Nếu unique constraint violation (round đã được tạo bởi process khác)
                        // CHỈ lấy round có seed deterministic
                        if ($e->getCode() == 23000) { // SQLSTATE[23000]: Integrity constraint violation
                            $round = self::where('round_number', $roundNumber)
                                ->where('seed', $expectedSeed)
                                ->first();
                        } else {
                            throw $e;
                        }
                    }
                }
            }
            
            // XÓA TẤT CẢ round có seed random (không phải deterministic) với cùng round_number
            // Chỉ giữ lại round có seed deterministic
            $roundsWithRandomSeed = self::where('round_number', $roundNumber)
                ->where('seed', '!=', $expectedSeed)
                ->get();
            
            foreach ($roundsWithRandomSeed as $randomRound) {
                // Chỉ xóa round có seed random nếu nó chưa finish và không có bets
                $hasBets = $randomRound->bets()->count() > 0;
                if (!$hasBets && $randomRound->status !== 'finished') {
                    $randomRound->delete();
                } elseif ($hasBets || $randomRound->status === 'finished') {
                    // Nếu round có seed random nhưng đã có bets hoặc đã finish
                    // Cập nhật seed thành deterministic (nếu chưa finish)
                    if ($randomRound->status !== 'finished') {
                        $randomRound->seed = $expectedSeed;
                        $randomRound->save();
                    }
                }
        }
        
        return $round;
        });
    }

    /**
     * Start the round
     */
    public function start()
    {
        // Random first result
        $firstResult = \App\Http\Controllers\ExploreController::randomGemType();
        
        $this->update([
            'status' => 'running',
            'started_at' => now(),
            'current_second' => 1,
            'current_result' => $firstResult,
        ]);
    }

    /**
     * Update current second and result
     * Note: Logic finish() đã được xử lý trong ProcessRoundTimer command
     */
    public function updateSecond($second, $result)
    {
        $this->update([
            'current_second' => $second,
            'current_result' => $result,
        ]);
    }

    /**
     * Finish the round
     */
    public function finish($finalResult)
    {
        // Chỉ finish nếu chưa finish (tránh finish nhiều lần)
        if ($this->status === 'finished') {
            return; // Đã finish rồi, không làm gì
        }
        
        $this->update([
            'status' => 'finished',
            'final_result' => $finalResult,
            'ended_at' => now(),
            'break_until' => null,
        ]);
        
        // Refresh để đảm bảo có final_result
        $this->refresh();
        
        // Append to signal grid (lưu vào SystemSetting để tất cả user thấy giống nhau)
        $this->appendToSignalGrid();
        
        // Process all bets for this round
        $this->processBets();
    }
    
    /**
     * Append round to signal grid (lưu vào SystemSetting)
     */
    public function appendToSignalGrid()
    {
        if (!$this->final_result) {
            return;
        }
        
        try {
            // Lấy rounds hiện tại từ SystemSetting
            $stored = \App\Models\SystemSetting::getValue('signal_grid_rounds', '[]');
            $rounds = json_decode($stored, true);
            
            if (!is_array($rounds)) {
                $rounds = [];
            }
            
            // Kiểm tra xem round này đã có chưa (tránh duplicate)
            $existingIndex = null;
            foreach ($rounds as $index => $round) {
                if (isset($round['round_number']) && $round['round_number'] == $this->round_number) {
                    $existingIndex = $index;
                    break;
                }
            }
            
            if ($existingIndex !== null) {
                // Round đã có, cập nhật result
                $rounds[$existingIndex]['final_result'] = $this->final_result;
            } else {
                // Logic: Tab signal là slider không bao giờ dừng với 3 items
                // - Item 1: rounds[0-19] (20 rounds, đã fill đầy)
                // - Item 2: rounds[20-39] (20 rounds, đã fill đầy)
                // - Item 3: rounds[40-59] (20 rounds, đang fill)
                // Khi item 3 đầy (60 rounds), shift: xóa rounds[0-19], giữ rounds[20-59], thêm round mới
                // Kiểm tra: nếu có >= 60 rounds, shift TRƯỚC KHI thêm round mới
                if (count($rounds) >= 60) {
                    // Shift: Item 1 = Item 2 cũ, Item 2 = Item 3 cũ, Item 3 trống
                    // Xóa 20 rounds đầu (item 1 cũ), giữ 40 rounds tiếp theo (item 2+3 cũ)
                    $rounds = array_slice($rounds, 20); // Giữ rounds[20-59], bây giờ có 40 rounds
                }
                
                // Thêm round mới vào cuối (sẽ fill vào item 3, hoặc item mới nếu vừa shift)
                $rounds[] = [
                    'round_number' => $this->round_number,
                    'final_result' => $this->final_result,
                ];
                
                // Đảm bảo không vượt quá 60 rounds
                if (count($rounds) > 60) {
                    $rounds = array_slice($rounds, -60); // Lấy 60 rounds cuối
                }
            }
            
            // Lưu lại vào SystemSetting
            \App\Models\SystemSetting::setValue('signal_grid_rounds', json_encode($rounds), 'Signal grid rounds (60 rounds: 3 items x 20 rounds each)');
        } catch (\Exception $e) {
            \Log::error("Error appending round to signal grid: " . $e->getMessage());
        }
    }

    /**
     * Process all bets and update user balances
     */
    public function processBets()
    {
        // Đảm bảo round đã có final_result
        if (!$this->final_result) {
            \Log::warning("Round {$this->id} cannot process bets: no final_result");
            return;
        }
        
        $bets = $this->bets()->where('status', 'pending')->get();
        
        if ($bets->isEmpty()) {
            \Log::info("Round {$this->id}: No pending bets to process");
            return; // Không có bets nào cần xử lý
        }
        
        \Log::info("Processing {$bets->count()} bets for round {$this->id} with final_result: {$this->final_result}");
        
        // Use transaction to ensure data consistency
        \DB::beginTransaction();
        try {
        foreach ($bets as $bet) {
            try {
            if ($bet->gem_type === $this->final_result) {
                // User won
                    $payoutAmount = $bet->amount * $bet->payout_rate;
                    
                    // Get user with lock to prevent race condition
                    $user = \App\Models\User::where('id', $bet->user_id)->lockForUpdate()->first();
                    
                    if (!$user) {
                        \Log::error("Bet {$bet->id}: User {$bet->user_id} not found");
                        continue;
                    }
                    
                    // Update bet status first
                $bet->update([
                    'status' => 'won',
                        'payout_amount' => $payoutAmount,
                ]);
                
                // Add winnings to user balance
                    $oldBalance = $user->balance;
                    $user->balance += $payoutAmount;
                    $user->save();
                    
                    // Refresh user to ensure balance is updated
                    $user->refresh();
                    
                    \Log::info("Bet {$bet->id}: User {$user->id} won {$payoutAmount}. Balance: {$oldBalance} -> {$user->balance}");
            } else {
                // User lost
                $bet->update([
                    'status' => 'lost',
                ]);
                // Balance was already deducted when bet was placed
                    
                    \Log::info("Bet {$bet->id}: User {$bet->user_id} lost");
                }
            } catch (\Exception $e) {
                \Log::error("Error processing bet {$bet->id}: " . $e->getMessage());
                \Log::error("Stack trace: " . $e->getTraceAsString());
                // Continue processing other bets even if one fails
            }
            }
            
            // Commit transaction after all bets processed
            \DB::commit();
            \Log::info("Round {$this->id}: Successfully processed all bets");
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("Round {$this->id}: Failed to process bets - " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
        }
        
        // After all bets are processed, update the status of related commissions to 'available'
        // Commissions are now available for withdrawal
        // Lấy tất cả bet_id của round này
        $betIds = $this->bets()->pluck('id')->toArray();
        
        if (!empty($betIds)) {
            // Update commission thông qua bet_id (vì user_commissions không có round_id)
            \App\Models\UserCommission::whereIn('bet_id', $betIds)
                ->where('status', 'pending')
                ->update(['status' => 'available']);
        }
        
        \Log::info("Finished processing bets for round {$this->id}");
    }
}
