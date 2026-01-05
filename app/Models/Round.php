<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_key',
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
     * IMPORTANT: Use UTC timezone to match client-side calculation
     */
    public static function calculateRoundNumber()
    {
        // Parse BASE_TIME as UTC (matching client: '2025-01-01T00:00:00Z')
        $baseTime = \Carbon\Carbon::parse(self::BASE_TIME . ' UTC')->timestamp;
        // Use UTC for now() to match client-side Date.now()
        $now = \Carbon\Carbon::now('UTC')->timestamp;
        $elapsed = $now - $baseTime;
        $totalCycle = 60; // 60 giây mỗi cycle
        return floor($elapsed / $totalCycle) + 1;
    }
    
    /**
     * Get current active round WITHOUT creating new one
     * Round should only be created by ProcessRoundTimer command
     */
    public static function getCurrentRound(string $gameKey = 'khaithac')
    {
        // Tính round number từ BASE_TIME
        $roundNumber = self::calculateRoundNumber();
        $expectedSeed = $gameKey . '_round_' . $roundNumber;
        
        // CHỈ lấy round có seed deterministic (round_ + roundNumber), BỎ round có seed random
        return self::where('game_key', $gameKey)
            ->where('round_number', $roundNumber)
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
    public static function getOrCreateRoundByNumber($roundNumber, string $gameKey = 'khaithac')
    {
        // Sử dụng lock để tránh race condition khi nhiều process cùng tạo round
        $round = \DB::transaction(function () use ($roundNumber, $gameKey) {
            $expectedSeed = $gameKey . '_round_' . $roundNumber;
            
            // CHỈ tìm round có seed deterministic (round_ + roundNumber)
            // BỎ TẤT CẢ round có seed random
            $round = self::where('game_key', $gameKey)
                ->where('round_number', $roundNumber)
                ->where('seed', $expectedSeed)
                ->lockForUpdate()
                ->first();
            
            if (!$round) {
                // Double-check sau khi lock để tránh race condition
                $round = self::where('game_key', $gameKey)
                    ->where('round_number', $roundNumber)
                    ->where('seed', $expectedSeed)
                    ->first();
                
                if (!$round) {
                    // Generate deterministic seed for this round (phải giống client)
                    // Client dùng: 'round_' + roundNumber
                    $seed = $gameKey . '_round_' . $roundNumber;
                    
                    try {
            $round = self::create([
                            'game_key' => $gameKey,
                            'round_number' => $roundNumber,
                'seed' => $seed,
                'status' => 'pending',
                'current_second' => 0,
            ]);
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Nếu unique constraint violation (round đã được tạo bởi process khác)
                        // CHỈ lấy round có seed deterministic
                        if ($e->getCode() == 23000) { // SQLSTATE[23000]: Integrity constraint violation
                            $round = self::where('game_key', $gameKey)
                                ->where('round_number', $roundNumber)
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
            $roundsWithRandomSeed = self::where('game_key', $gameKey)
                ->where('round_number', $roundNumber)
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
        
        // Cleanup old rounds sau khi tạo round mới (ngoài transaction để tránh deadlock)
        // Chỉ cleanup khi round vừa được tạo trong transaction này
        try {
            self::cleanupOldRounds($gameKey);
        } catch (\Exception $e) {
            // Log error nhưng không throw để không ảnh hưởng đến việc tạo round
            \Log::warning('Error cleaning up old rounds: ' . $e->getMessage());
        }
        
        return $round;
    }

    /**
     * Cleanup old rounds - chỉ giữ lại tối đa 60 rounds gần nhất
     * Xóa các round cũ nhất, nhưng chỉ xóa các round đã finish và không có bets
     */
    public static function cleanupOldRounds(string $gameKey = 'khaithac')
    {
        // Đếm tổng số rounds
        $totalRounds = self::where('game_key', $gameKey)->count();
        
        // Nếu có nhiều hơn 60 rounds, cần xóa các round cũ
        if ($totalRounds > 60) {
            // Lấy 60 rounds gần nhất (theo round_number desc)
            $latestRounds = self::where('game_key', $gameKey)
                ->orderBy('round_number', 'desc')
                ->limit(60)
                ->pluck('id')
                ->toArray();
            
            // Xóa các round không nằm trong danh sách 60 rounds gần nhất
            // Nhưng chỉ xóa các round đã finish và không có bets
            $roundsToDelete = self::where('game_key', $gameKey)
                ->whereNotIn('id', $latestRounds)
                ->where('status', 'finished')
                ->whereDoesntHave('bets')
                ->get();
            
            foreach ($roundsToDelete as $round) {
                $round->delete();
            }
            
            // Nếu vẫn còn nhiều hơn 60 rounds sau khi xóa các round không có bets
            // Xóa các round cũ nhất (đã finish) ngay cả khi có bets (nhưng chỉ khi bets đã được xử lý)
            $remainingCount = self::where('game_key', $gameKey)->count();
            if ($remainingCount > 60) {
                $excessCount = $remainingCount - 60;
                $oldestRounds = self::where('game_key', $gameKey)
                    ->orderBy('round_number', 'asc')
                    ->where('status', 'finished')
                    ->limit($excessCount)
                    ->get();
                
                foreach ($oldestRounds as $round) {
                    // Chỉ xóa nếu tất cả bets đã được xử lý (không còn pending)
                    $pendingBets = $round->bets()->where('status', 'pending')->count();
                    if ($pendingBets === 0) {
                        $round->delete();
                    }
                }
            }
        }
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
        
        // Cleanup old rounds sau khi finish round
        self::cleanupOldRounds($this->game_key ?? 'khaithac');
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
            $gameKey = $this->game_key ?? 'khaithac';
            $settingKey = 'signal_grid_rounds_' . $gameKey;

            // Lấy rounds hiện tại từ SystemSetting
            $stored = \App\Models\SystemSetting::getValue($settingKey, null);
            // Backward-compat: migrate from old key once for khaithac
            if ($stored === null && $gameKey === 'khaithac') {
                $stored = \App\Models\SystemSetting::getValue('signal_grid_rounds', '[]');
            }
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
                // Logic: Tab signal là slider không bao giờ dừng với 3 cột
                // - Cột 1: rounds[0-15] (16 rounds = 4 hàng x 4 items, đã fill đầy)
                // - Cột 2: rounds[16-31] (16 rounds = 4 hàng x 4 items, đã fill đầy)
                // - Cột 3: rounds[32-47] (16 rounds = 4 hàng x 4 items, đang fill)
                // Khi cột 3 đầy (48 rounds), shift: xóa rounds[0-15] (cột 1 cũ), giữ rounds[16-47], thêm round mới vào cột 3 trống
                // Kiểm tra: nếu có >= 48 rounds, shift TRƯỚC KHI thêm round mới
                if (count($rounds) >= 48) {
                    // Shift: Cột 1 = Cột 2 cũ, Cột 2 = Cột 3 cũ, Cột 3 trống
                    // Xóa 16 rounds đầu (cột 1 cũ), giữ 32 rounds tiếp theo (cột 2+3 cũ)
                    $rounds = array_slice($rounds, 16); // Giữ rounds[16-47], bây giờ có 32 rounds
                }
                
                // Thêm round mới vào cuối (sẽ fill vào cột 3, hoặc cột mới nếu vừa shift)
                $rounds[] = [
                    'round_number' => $this->round_number,
                    'final_result' => $this->final_result,
                ];
                
                // Đảm bảo không vượt quá 48 rounds
                if (count($rounds) > 48) {
                    $rounds = array_slice($rounds, -48); // Lấy 48 rounds cuối
                }
            }
            
            // Lưu lại vào SystemSetting (scoped per game)
            \App\Models\SystemSetting::setValue($settingKey, json_encode($rounds), "Signal grid rounds for {$gameKey} (48 rounds: 3 columns x 16 rounds each, 4 rows x 4 items)");
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
        
        // Check if this is a jackpot result (nổ hũ)
        $jackpotTypes = ['thachanhtim', 'ngusac', 'cuoc'];
        $isJackpot = in_array($this->final_result, $jackpotTypes);

        // Xanh đỏ rule:
        // If final result is "Tím" (daquy) then bets on Xanh/Đỏ are also winning,
        // but they still pay using their own payout_rate (stored on bet).
        $isXanhDo = ($this->game_key ?? 'khaithac') === 'xanhdo';
        $isPurpleWild = $isXanhDo && $this->final_result === 'daquy';
        
        // Get jackpot payout rate if it's a jackpot
        $jackpotPayoutRate = null;
        if ($isJackpot) {
            // Default rates for each jackpot type
            $defaultRates = [
                'thachanhtim' => 10.00,
                'ngusac' => 20.00,
                'cuoc' => 50.00,
            ];
            $defaultRate = $defaultRates[$this->final_result] ?? 10.00;
            $jackpotPayoutRate = (float) \App\Models\SystemSetting::getValue('gem_payout_rate_' . $this->final_result, (string) $defaultRate);
            \Log::info("Round {$this->id}: Jackpot detected - type: {$this->final_result}, payout_rate: {$jackpotPayoutRate}");
        }
        
        // Use transaction to ensure data consistency
        \DB::beginTransaction();
        try {
            foreach ($bets as $bet) {
                try {
                    if ($isJackpot) {
                        // Nổ hũ: Tất cả bets đều thắng với tỉ lệ của hũ
                        $payoutAmount = (float) $bet->amount * (float) $jackpotPayoutRate;
                        
                        \Log::info("Bet {$bet->id}: Processing JACKPOT - amount: {$bet->amount}, rate: {$jackpotPayoutRate}, payout: {$payoutAmount}");
                        
                        // Get user with lock to prevent race condition
                        $user = \App\Models\User::where('id', $bet->user_id)->lockForUpdate()->first();
                        
                        if (!$user) {
                            \Log::error("Bet {$bet->id}: User {$bet->user_id} not found");
                            continue;
                        }
                        
                        // Get old balance before update
                        $oldBalance = (float) $user->balance;
                        $newBalance = $oldBalance + $payoutAmount;
                        
                        // Update bet status FIRST - tất cả đều thắng
                        $bet->status = 'won';
                        $bet->payout_amount = $payoutAmount;
                        $bet->payout_rate = $jackpotPayoutRate; // Update payout rate to jackpot rate
                        $bet->save();
                        
                        // Then update user balance
                        $user->balance = $newBalance;
                        $user->save();
                        
                        // Refresh to verify
                        $bet->refresh();
                        $user->refresh();
                        
                        \Log::info("Bet {$bet->id}: User {$user->id} won JACKPOT - Bet status: {$bet->status}, Payout: {$payoutAmount}, Rate: {$bet->payout_rate}, Balance: {$oldBalance} -> {$user->balance}");
                        
                        // Final verification
                        if ($bet->status !== 'won') {
                            \Log::error("Bet {$bet->id}: Status is not 'won' after update! Status: {$bet->status}");
                        }
                        if (abs((float) $user->balance - $newBalance) > 0.01) {
                            \Log::error("Bet {$bet->id}: Balance mismatch! Expected: {$newBalance}, Actual: {$user->balance}");
                        }
                    } else if (
                        $bet->gem_type === $this->final_result
                        || ($isPurpleWild && in_array($bet->gem_type, ['kcxanh', 'kcdo', 'daquy'], true))
                    ) {
                        // User won (normal result)
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
                \Log::info("Round {$this->id}: Successfully processed all bets. Transaction committed.");
                
                // Final verification: Check all bets were processed correctly
                $this->refresh();
                $processedBets = $this->bets()->whereIn('status', ['won', 'lost'])->get();
                \Log::info("Round {$this->id}: After processing - Won: " . $processedBets->where('status', 'won')->count() . ", Lost: " . $processedBets->where('status', 'lost')->count());
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error("Round {$this->id}: Failed to process bets - " . $e->getMessage());
                \Log::error("Stack trace: " . $e->getTraceAsString());
                throw $e; // Re-throw to ensure error is logged
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

    /**
     * Random kết quả dựa vào tổng tiền đặt cược
     * Đá có nhiều tiền đặt cược nhất sẽ KHÔNG được random (không thắng)
     * Random trong 2 đá còn lại
     */
    public function randomResultBasedOnBets()
    {
        // Lấy tất cả bets của round này
        $bets = $this->bets()->where('status', 'pending')->get();
        
        // Tính tổng tiền đặt cược cho mỗi đá
        $betAmounts = [
            'kcxanh' => 0,
            'daquy' => 0,
            'kcdo' => 0,
        ];
        
        foreach ($bets as $bet) {
            if (isset($betAmounts[$bet->gem_type])) {
                $betAmounts[$bet->gem_type] += (float) $bet->amount;
            }
        }
        
        // Tìm đá có nhiều tiền nhất
        $maxAmount = max($betAmounts);
        $excludedGems = [];
        
        foreach ($betAmounts as $gemType => $amount) {
            if ($amount === $maxAmount && $maxAmount > 0) {
                $excludedGems[] = $gemType;
            }
        }
        
        // Nếu có nhiều đá cùng số tiền cao nhất, loại trừ tất cả
        // Nếu không có bets nào, random bình thường trong cả 3 đá
        $availableGems = ['kcxanh', 'daquy', 'kcdo'];
        
        if (!empty($excludedGems)) {
            // Loại trừ đá có nhiều tiền nhất
            $availableGems = array_diff($availableGems, $excludedGems);
        }
        
        // Nếu chỉ còn 1 đá, dùng đá đó
        if (count($availableGems) === 1) {
            return reset($availableGems);
        }
        
        // Nếu còn 2 đá, random trong 2 đá đó
        // Nếu không có bets nào (cả 3 đá đều có thể), random trong cả 3 đá
        // Sử dụng seed của round để đảm bảo deterministic
        $seed = $this->seed . '_final_result';
        $hash = 0;
        for ($i = 0; $i < strlen($seed); $i++) {
            $char = ord($seed[$i]);
            $hash = (($hash << 5) - $hash) + $char;
            $hash = $hash & 0x7FFFFFFF;
        }
        
        $rand = (abs($hash) % 10000) % 100 + 1;
        
        // Random trong các đá còn lại
        $availableGemsArray = array_values($availableGems);
        $index = ($rand - 1) % count($availableGemsArray);
        
        return $availableGemsArray[$index];
    }
}
