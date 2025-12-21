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
     * Round duration: 60 giây, Break time: 10 giây, Total cycle: 70 giây
     */
    public static function calculateRoundNumber()
    {
        $baseTime = \Carbon\Carbon::parse(self::BASE_TIME)->timestamp;
        $now = now()->timestamp;
        $elapsed = $now - $baseTime;
        $breakTime = 10; // 10 giây break time
        $totalCycle = 60 + $breakTime; // 70 giây mỗi cycle
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
        
        // Set break time: 10 seconds after round ends (để hiển thị kết quả và bets)
        $breakUntil = now()->addSeconds(10);
        
        $this->update([
            'status' => 'finished',
            'final_result' => $finalResult,
            'ended_at' => now(),
            'break_until' => $breakUntil,
        ]);
        
        // Refresh để đảm bảo có final_result
        $this->refresh();
        
        // Process all bets for this round
        $this->processBets();
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
            return; // Không có bets nào cần xử lý
        }
        
        \Log::info("Processing {$bets->count()} bets for round {$this->id} with final_result: {$this->final_result}");
        
        foreach ($bets as $bet) {
            try {
            if ($bet->gem_type === $this->final_result) {
                // User won
                    $payoutAmount = $bet->amount * $bet->payout_rate;
                    
                $bet->update([
                    'status' => 'won',
                        'payout_amount' => $payoutAmount,
                ]);
                
                // Add winnings to user balance
                    $user = $bet->user;
                    $user->balance += $payoutAmount;
                    $user->save();
                    
                    \Log::info("Bet {$bet->id}: User {$user->id} won {$payoutAmount}");
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
            }
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
