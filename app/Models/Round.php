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
            
            // Đảm bảo round không null trước khi xử lý
            if (!$round) {
                \Log::error("Failed to get or create round {$roundNumber} for game {$gameKey}");
                throw new \Exception("Failed to get or create round {$roundNumber} for game {$gameKey}");
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
        
        // KHÔNG cleanup ngay sau khi tạo round mới
        // Cleanup sẽ được thực hiện định kỳ hoặc khi thực sự cần (ví dụ: > 500 rounds)
        // Điều này đảm bảo lịch sử cược luôn có sẵn cho người dùng
        
        // Đảm bảo không trả về null
        if (!$round) {
            \Log::error("getOrCreateRoundByNumber returned null for round {$roundNumber} game {$gameKey}");
            // Thử lấy lại một lần nữa
            $round = self::where('game_key', $gameKey)
                ->where('round_number', $roundNumber)
                ->where('seed', $gameKey . '_round_' . $roundNumber)
                ->first();
        }
        
        return $round;
    }

    /**
     * Cleanup old rounds - chỉ giữ lại tối đa 500 rounds gần nhất
     * Chỉ cleanup khi có > 500 rounds để đảm bảo có đủ lịch sử cho người dùng
     * Xóa các round cũ nhất, nhưng chỉ xóa các round đã finish và không có bets
     * KHÔNG BAO GIỜ xóa rounds có bets
     */
    public static function cleanupOldRounds(string $gameKey = 'khaithac')
    {
        // Đếm tổng số rounds
        $totalRounds = self::where('game_key', $gameKey)->count();
        
        // Chỉ cleanup khi có nhiều hơn 500 rounds (để đảm bảo có đủ lịch sử)
        if ($totalRounds > 500) {
            // Lấy 500 rounds gần nhất (theo round_number desc)
            $latestRounds = self::where('game_key', $gameKey)
                ->orderBy('round_number', 'desc')
                ->limit(500)
                ->pluck('id')
                ->toArray();
            
            // Xóa các round không nằm trong danh sách 500 rounds gần nhất
            // NHƯNG chỉ xóa các round đã finish và KHÔNG có bets (KHÔNG BAO GIỜ xóa rounds có bets)
            $roundsToDelete = self::where('game_key', $gameKey)
                ->whereNotIn('id', $latestRounds)
                ->where('status', 'finished')
                ->whereDoesntHave('bets') // CHỈ xóa rounds không có bets
                ->get();
            
            $deletedCount = 0;
            foreach ($roundsToDelete as $round) {
                // Double-check: đảm bảo không có bets trước khi xóa
                $hasBets = $round->bets()->count() > 0;
                if (!$hasBets) {
                    $round->delete();
                    $deletedCount++;
                }
            }
            
            if ($deletedCount > 0) {
                \Log::info("Cleaned up {$deletedCount} old rounds for game {$gameKey}");
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
        
        // KHÔNG cleanup ngay sau khi finish round
        // Cleanup sẽ được thực hiện định kỳ hoặc khi thực sự cần (> 500 rounds)
        // Điều này đảm bảo lịch sử cược luôn có sẵn cho người dùng
    }
    
    /**
     * Append round to signal grid (lưu vào SystemSetting)
     */
    public function appendToSignalGrid()
    {
        // final_result can be string "0" (valid) => do NOT use truthy checks
        if ($this->final_result === null || $this->final_result === '') {
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
     * Check if a bet is winning for xanhdo game
     * Logic:
     * - If bet is on a number (bet_type = 'number'), ONLY check if bet_value matches result number (ignore color)
     * - If bet is on a color (bet_type = 'color' or null), check if gem_type is in winning colors
     */
    private function isWinningBet($bet, $resultNum, $winningGemTypes)
    {
        // Check number bets: if bet_type is 'number', ONLY check the number match
        if ($bet->bet_type === 'number' && $bet->bet_value !== null) {
            $betNumber = (int) $bet->bet_value;
            // For number bets, ONLY the exact number wins, colors don't matter
            return ($betNumber === $resultNum);
        }
        
        // Check color bets: if gem_type is in winning colors
        // This applies to:
        // - Color bets (bet_type = 'color')
        // - Legacy bets (bet_type = null) - treat as color bet
        if (in_array($bet->gem_type, $winningGemTypes, true)) {
            return true; // Color bet matches one of the winning colors
        }
        
        return false;
    }

    /**
     * Process all bets and update user balances
     */
    public function processBets()
    {
        // Đảm bảo round đã có final_result
        // final_result can be string "0" (valid) => do NOT use truthy checks
        if ($this->final_result === null || $this->final_result === '') {
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
        // For xanhdo game, final_result is a number (0-9)
        // When a number is drawn, bets on that number AND all corresponding colors win
        // Logic:
        // - Number bets: stored with gem_type = 'number_0', 'number_1', etc. (need to check bet table structure)
        // - Color bets: stored with gem_type = 'kcxanh', 'kcdo', 'daquy'
        // Winning conditions:
        // 0: số 0, màu đỏ (kcdo), màu tím (daquy)
        // 1: số 1, màu xanh (kcxanh)
        // 2: số 2, màu đỏ (kcdo)
        // 3: số 3, màu xanh (kcxanh)
        // 4: số 4, màu đỏ (kcdo)
        // 5: số 5, màu tím (daquy), màu xanh (kcxanh)
        // 6: số 6, màu đỏ (kcdo)
        // 7: số 7, màu xanh (kcxanh)
        // 8: số 8, màu đỏ (kcdo)
        // 9: số 9, màu xanh (kcxanh)
        $isXanhDo = ($this->game_key ?? 'khaithac') === 'xanhdo';
        $winningGemTypes = [];
        $winningNumbers = [];
        
        if ($isXanhDo && is_numeric($this->final_result)) {
            $resultNum = (int) $this->final_result;
            $winningNumbers[] = $resultNum; // The number itself wins
            
            // Determine winning colors based on number
            // 1,3,7,9: xanh (kcxanh)
            // 2,4,6,8: đỏ (kcdo)
            // 0: tím (daquy) + đỏ (kcdo)
            // 5: tím (daquy) + xanh (kcxanh)
            if ($resultNum === 0) {
                $winningGemTypes = ['daquy', 'kcdo']; // tím + đỏ
            } elseif (in_array($resultNum, [1, 3, 7, 9])) {
                $winningGemTypes = ['kcxanh']; // xanh
            } elseif (in_array($resultNum, [2, 4, 6, 8])) {
                $winningGemTypes = ['kcdo']; // đỏ
            } elseif ($resultNum === 5) {
                $winningGemTypes = ['daquy', 'kcxanh']; // tím + xanh
            }
        }
        
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
                        (!$isXanhDo && $bet->gem_type === $this->final_result)
                        || ($isXanhDo && $this->isWinningBet($bet, $resultNum, $winningGemTypes))
                    ) {
                        // For xanhdo: When a number is drawn, bets on that number AND all corresponding colors win
                        // Example: Number 1 → bets on number 1 (stored as kcxanh) AND color xanh (kcxanh) both win
                        // Example: Number 0 → bets on number 0 (stored as kcdo or daquy) AND color đỏ (kcdo) AND color tím (daquy) all win
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
     * For xanhdo game, returns a number (0-9) instead of gem type
     */
    public function randomResultBasedOnBets()
    {
        $isXanhDo = ($this->game_key ?? 'khaithac') === 'xanhdo';
        
        if ($isXanhDo) {
            // For xanhdo, return a random number 0-9
            // Số 0 và 5 có tỉ lệ rất thấp (mỗi số 2%), các số khác chia đều 96%
            $random = rand(1, 100);
            
            if ($random <= 2) {
                // 2% - số 0
                return '0';
            } elseif ($random <= 4) {
                // 2% - số 5
                return '5';
            } else {
                // 96% - các số còn lại (1,2,3,4,6,7,8,9) - mỗi số ~12%
                $otherNumbers = [1, 2, 3, 4, 6, 7, 8, 9];
                $index = rand(0, count($otherNumbers) - 1);
                return (string) $otherNumbers[$index];
            }
        }
        
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
