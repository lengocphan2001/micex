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
     * Get current active round or create new one
     * @deprecated Use getOrCreateRoundByNumber() instead
     */
    public static function getCurrentRound()
    {
        // Tính round number từ BASE_TIME
        // Round duration: 60 giây, Break time: 10 giây, Total cycle: 70 giây
        $baseTime = \Carbon\Carbon::parse(self::BASE_TIME)->timestamp;
        $now = now()->timestamp;
        $elapsed = $now - $baseTime;
        $breakTime = 10; // 10 giây break time
        $totalCycle = 60 + $breakTime; // 70 giây mỗi cycle
        $roundNumber = floor($elapsed / $totalCycle) + 1;
        
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
            // Tìm round với round_number này (với lock)
            $round = self::where('round_number', $roundNumber)->lockForUpdate()->first();
            
            if (!$round) {
                // Generate deterministic seed for this round (phải giống client)
                // Client dùng: 'round_' + roundNumber
                $seed = 'round_' . $roundNumber;
                $round = self::create([
                    'round_number' => $roundNumber,
                    'seed' => $seed,
                    'status' => 'pending',
                    'current_second' => 0,
                ]);
            } else {
                // Nếu round đã tồn tại nhưng seed không phải deterministic, cập nhật lại
                // (chỉ cập nhật nếu round chưa finish để không ảnh hưởng kết quả đã lưu)
                $expectedSeed = 'round_' . $roundNumber;
                if ($round->seed !== $expectedSeed && $round->status !== 'finished') {
                    $round->seed = $expectedSeed;
                    $round->save();
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
