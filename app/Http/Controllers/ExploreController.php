<?php

namespace App\Http\Controllers;

use App\Models\Round;
use App\Models\Bet;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExploreController extends Controller
{
    /**
     * Show explore page
     */
    public function index()
    {
        return view('explore');
    }

    // Tỉ lệ random cho mỗi loại đá (tổng = 100)
    private const GEM_RANDOM_RATES = [
        'thachanh' => 30,      // 30%
        'thachanhtim' => 25,   // 25%
        'ngusac' => 20,        // 20%
        'daquy' => 15,         // 15%
        'cuoc' => 7,           // 7%
        'kimcuong' => 3,       // 3%
    ];

    // Tỉ lệ ăn cho mỗi loại đá (default values, can be overridden from database)
    private const GEM_PAYOUT_RATES_DEFAULT = [
        'thachanh' => 2.0,
        'thachanhtim' => 2.5,
        'ngusac' => 3.0,
        'daquy' => 4.0,
        'cuoc' => 5.0,
        'kimcuong' => 5.95,
    ];
    
    /**
     * Get payout rates from database or use defaults
     */
    private function getPayoutRates()
    {
        return [
            'thachanh' => (float) SystemSetting::getValue('gem_payout_rate_thachanh', self::GEM_PAYOUT_RATES_DEFAULT['thachanh']),
            'thachanhtim' => (float) SystemSetting::getValue('gem_payout_rate_thachanhtim', self::GEM_PAYOUT_RATES_DEFAULT['thachanhtim']),
            'ngusac' => (float) SystemSetting::getValue('gem_payout_rate_ngusac', self::GEM_PAYOUT_RATES_DEFAULT['ngusac']),
            'daquy' => (float) SystemSetting::getValue('gem_payout_rate_daquy', self::GEM_PAYOUT_RATES_DEFAULT['daquy']),
            'cuoc' => (float) SystemSetting::getValue('gem_payout_rate_cuoc', self::GEM_PAYOUT_RATES_DEFAULT['cuoc']),
            'kimcuong' => (float) SystemSetting::getValue('gem_payout_rate_kimcuong', self::GEM_PAYOUT_RATES_DEFAULT['kimcuong']),
        ];
    }

    /**
     * Get current round status
     */
    public function getCurrentRound()
    {
        // Lưu ý: Round được tạo và quản lý bởi ProcessRoundTimer command (background)
        // Không tự động tạo hoặc start round ở đây để tránh duplicate
        // CHỈ lấy round có seed deterministic (round_ + roundNumber), BỎ round có seed random
        $round = Round::getCurrentRound();
        
        // Nếu round chưa tồn tại, trả về null (không tạo mới)
        // Round sẽ được tạo bởi ProcessRoundTimer command
        if (!$round) {
            return response()->json([
                'round' => null,
                'message' => 'Round not found. Please wait for ProcessRoundTimer to create it.',
            ]);
        }
        
        // Không auto-start round ở đây vì ProcessRoundTimer đã xử lý
        
        // Calculate current phase based on started_at
        $phase = 'break'; // break, betting, result
        $currentSecond = 0;
        $isInBreak = false;
        
        if ($round->status === 'running' && $round->started_at) {
            $elapsedSeconds = now()->diffInSeconds($round->started_at);
            $currentSecond = min(60, $elapsedSeconds + 1);
            
            if ($currentSecond <= 30) {
                $phase = 'betting';
            } else {
                $phase = 'result';
            }
        } elseif ($round->status === 'finished') {
            $phase = 'break';
            $isInBreak = $round->break_until && now()->lt($round->break_until);
        }
        
        return response()->json([
            'round' => [
                'id' => $round->id,
                'round_number' => $round->round_number,
                'seed' => $round->seed,
                'status' => $round->status,
                'phase' => $phase,
                'current_second' => $currentSecond,
                'final_result' => $round->final_result,
                'admin_set_result' => $round->admin_set_result, // Kết quả admin đặt (nếu có)
                'started_at' => $round->started_at?->toIso8601String(),
                'break_until' => $round->break_until?->toIso8601String(),
                'is_in_break' => $isInBreak,
            ],
            'gem_types' => $this->getGemTypes(),
        ]);
    }

    /**
     * Save round result when client finishes the round (chỉ gọi 1 lần khi round kết thúc)
     */
    public function saveResult(Request $request)
    {
        try {
        $validated = $request->validate([
            'round_id' => 'required|exists:rounds,id',
            'final_result' => 'required|in:thachanh,thachanhtim,ngusac,daquy,cuoc,kimcuong',
            'results' => 'required|array|size:60', // Mảng chính xác 60 phần tử
            'results.*' => 'nullable|in:thachanh,thachanhtim,ngusac,daquy,cuoc,kimcuong', // Cho phép null cho 30 giây đầu
        ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for saveResult:', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
        
        $round = Round::findOrFail($validated['round_id']);
        
        // Only allow saving if round is still running
        if ($round->status !== 'running') {
            return response()->json([
                'error' => 'Round is not running',
            ], 400);
        }
        
        // Đảm bảo mảng results có đúng 60 phần tử và không có null/undefined
        $results = array_values($validated['results']); // Re-index array và loại bỏ keys không liên tục
        if (count($results) !== 60) {
            \Log::error("Round {$round->id} results array has wrong length: " . count($results));
            return response()->json([
                'error' => 'Results array must have exactly 60 elements',
            ], 422);
        }
        
        // Determine the final result: use admin_set_result if exists, otherwise use client's result
        $finalResult = $round->admin_set_result ?? $validated['final_result'];
        
        // Verify the final result matches what server would calculate (if no admin intervention)
        if (!$round->admin_set_result) {
            $expectedResult = $this->getGemForSecond($round->seed, 60, $round);
            if ($validated['final_result'] !== $expectedResult) {
                // Log warning but still accept (client might have slight timing difference)
                \Log::warning("Round {$round->id} final result mismatch: expected {$expectedResult}, got {$validated['final_result']}");
            }
        } else {
            // Admin has set result, use it and update results array
            // Don't log warning for mismatch when admin has set result
            $finalResult = $round->admin_set_result;
        }
        
        // Verify all results match và fill các phần tử rỗng
        // 30 giây đầu: có thể null (không random)
        // 30 giây cuối: phải có giá trị hợp lệ
        foreach ($results as $index => $result) {
            if ($index < 30) {
                // 30 giây đầu: cho phép null, không cần verify
                if ($result === null) {
                    continue;
                }
            }
            
            // 30 giây cuối: phải có giá trị hợp lệ
            if (empty($result) || !in_array($result, ['thachanh', 'thachanhtim', 'ngusac', 'daquy', 'cuoc', 'kimcuong'])) {
                \Log::warning("Round {$round->id} result at second " . ($index + 1) . " is invalid: {$result}, filling with expected value");
                // Fill with expected value (chỉ random từ giây 31-60)
                if ($index >= 30) {
                    $results[$index] = $this->getGemForSecond($round->seed, $index + 1, $round);
                }
            } else {
                // Verify result matches expected (chỉ verify từ giây 31-60)
                if ($index >= 30) {
                    $expected = $this->getGemForSecond($round->seed, $index + 1, $round);
                    if ($result !== $expected) {
                        \Log::warning("Round {$round->id} result at second " . ($index + 1) . " mismatch: expected {$expected}, got {$result}");
                    }
                }
            }
        }
        
        // If admin has set result, update the last result (second 60) to match
        if ($round->admin_set_result) {
            $results[59] = $round->admin_set_result; // Index 59 = second 60
            $finalResult = $round->admin_set_result;
        }
        
        // Save results array to round
        $round->results = $results;
        $round->save();
        
        // Finish the round with the final result (admin_set_result if exists, otherwise client's result)
        $round->finish($finalResult);
        
        return response()->json([
            'success' => true,
            'message' => 'Round result saved',
        ]);
    }
    
    /**
     * Get gem type for a specific second based on seed (deterministic random)
     * This must match the client-side logic exactly
     * Improved hash function to avoid consecutive duplicates
     * If second = 60 and round has admin_set_result, return admin_set_result
     */
    public function getGemForSecond($seed, $second, $round = null)
    {
        // If it's the last second (60) and admin has set a result, use that
        if ($second === 60 && $round && $round->admin_set_result) {
            return $round->admin_set_result;
        }
        
        // Use seed + second to create a deterministic random value
        // Improved hash function with better distribution
        $string = $seed . '_' . $second;
        $hash = 0;
        for ($i = 0; $i < strlen($string); $i++) {
            $char = ord($string[$i]);
            $hash = (($hash << 5) - $hash) + $char;
            $hash = $hash & 0x7FFFFFFF; // Convert to 32bit integer
        }
        
        // Add second to hash for better variation
        $hash = (($hash * 31 + $second * 17) & 0x7FFFFFFF);
        
        // Convert to 1-100 range with better distribution
        $rand = (abs($hash) % 10000) % 100 + 1;
        
        $cumulative = 0;
        foreach (self::GEM_RANDOM_RATES as $type => $rate) {
            $cumulative += $rate;
            if ($rand <= $cumulative) {
                return $type;
            }
        }
        
        return 'thachanh';
    }

    /**
     * Place a bet
     */
    public function placeBet(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'gem_type' => 'required|in:thachanh,thachanhtim,ngusac,daquy,cuoc,kimcuong',
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Get current round (không tạo mới, chỉ lấy từ database)
        $round = Round::getCurrentRound();
        
        if (!$round) {
            return response()->json(['error' => 'Round not found'], 404);
        }
        
        // Calculate current second
        $currentSecond = 0;
        if ($round->status === 'running' && $round->started_at) {
            $elapsedSeconds = now()->diffInSeconds($round->started_at);
            $currentSecond = min(60, $elapsedSeconds + 1);
        }
        
        // Check if round is still accepting bets (only in first 30 seconds)
        if ($round->status !== 'running' || $currentSecond > 30) {
            return response()->json([
                'error' => 'Thời gian đặt cược đã kết thúc. Chỉ có thể đặt cược trong 30 giây đầu của mỗi phiên.',
            ], 400);
        }

        // Check if user has enough balance
        if ($user->balance < $validated['amount']) {
            return response()->json([
                'error' => 'Số dư không đủ để đặt cược.',
            ], 400);
        }

        // Use database lock to prevent race condition when betting simultaneously
        DB::beginTransaction();
        try {
            // Lock the user row to prevent concurrent balance updates
            $user = User::where('id', $user->id)->lockForUpdate()->first();
            
            // Check if user already placed a bet in this round (with lock)
            $existingBet = Bet::where('round_id', $round->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();
            
            if ($existingBet) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Bạn đã đặt cược trong phiên này rồi.',
                ], 400);
            }

            // Check balance again after lock (in case it changed)
            if ($user->balance < $validated['amount']) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Số dư không đủ để đặt cược.',
                ], 400);
            }

            // Deduct balance
            $user->balance -= $validated['amount'];
            $user->save();

            // Create bet (unique constraint will prevent duplicates)
            try {
                $bet = Bet::create([
                    'round_id' => $round->id,
                    'user_id' => $user->id,
                    'gem_type' => $validated['gem_type'],
                    'amount' => $validated['amount'],
                    'payout_rate' => $this->getPayoutRates()[$validated['gem_type']],
                    'status' => 'pending',
                ]);
                
                // Calculate and distribute commission to network (F1, F2, F3...) based on bet amount
                // Commission được tính dựa trên khối lượng bet, không cần thắng
                $this->calculateCommission($bet, $user);
            } catch (\Illuminate\Database\QueryException $e) {
                // If unique constraint violation (duplicate bet)
                if ($e->getCode() == 23000) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Bạn đã đặt cược trong phiên này rồi.',
                    ], 400);
                }
                throw $e;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt cược thành công!',
                'bet' => [
                    'id' => $bet->id,
                    'gem_type' => $bet->gem_type,
                    'amount' => $bet->amount,
                    'payout_rate' => $bet->payout_rate,
                ],
                'new_balance' => $user->balance,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Có lỗi xảy ra khi đặt cược: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's bet for current round
     */
    public function getMyBet()
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $round = Round::getCurrentRound();
        
        if (!$round) {
            return response()->json(['bet' => null, 'balance' => $user->balance]);
        }
        
        $bet = Bet::where('round_id', $round->id)
            ->where('user_id', $user->id)
            ->with('round') // Eager load round để lấy final_result
            ->first();

        if (!$bet) {
            return response()->json(['bet' => null]);
        }

        // Refresh user to get latest balance
        $user->refresh();
        
        $betData = [
            'id' => $bet->id,
            'round_id' => $bet->round_id,
            'round_number' => $bet->round->round_number ?? null, // Thêm round_number để client kiểm tra
            'gem_type' => $bet->gem_type,
            'amount' => $bet->amount,
            'payout_rate' => $bet->payout_rate,
            'status' => $bet->status,
            'payout_amount' => $bet->payout_amount,
            'round' => [
                'id' => $bet->round->id ?? null,
                'round_number' => $bet->round->round_number ?? null,
                'final_result' => $bet->round->final_result ?? null, // Thêm final_result để client hiển thị
                'admin_set_result' => $bet->round->admin_set_result ?? null, // Thêm admin_set_result để client kiểm tra
            ],
        ];
        
        return response()->json([
            'bet' => $betData,
            'balance' => $user->balance, // Trả về balance mới nhất
        ]);
    }

    /**
     * Get bet amounts for current round (for realtime display)
     */
    public function getBetAmounts()
    {
        $round = Round::getCurrentRound();
        
        if (!$round) {
            return response()->json(['bet_amounts' => []]);
        }
        
        // Get total bet amounts for each gem type (only pending bets count)
        $betAmounts = Bet::where('round_id', $round->id)
            ->where('status', 'pending') // Only count pending bets
            ->select('gem_type', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as bet_count'))
            ->groupBy('gem_type')
            ->get()
            ->pluck('total_amount', 'gem_type')
            ->toArray();
        
        // Initialize all gem types with 0
        $allBetAmounts = [
            'thachanh' => isset($betAmounts['thachanh']) ? (float) $betAmounts['thachanh'] : 0,
            'thachanhtim' => isset($betAmounts['thachanhtim']) ? (float) $betAmounts['thachanhtim'] : 0,
            'ngusac' => isset($betAmounts['ngusac']) ? (float) $betAmounts['ngusac'] : 0,
            'daquy' => isset($betAmounts['daquy']) ? (float) $betAmounts['daquy'] : 0,
            'cuoc' => isset($betAmounts['cuoc']) ? (float) $betAmounts['cuoc'] : 0,
            'kimcuong' => isset($betAmounts['kimcuong']) ? (float) $betAmounts['kimcuong'] : 0,
        ];
        
        return response()->json([
            'bet_amounts' => $allBetAmounts,
        ]);
    }

    /**
     * Calculate and distribute commission to network when user places a bet
     * Commission được tính dựa trên khối lượng bet (bet amount), không cần thắng
     * Network: F1 = người giới thiệu user bet, F2 = người giới thiệu F1, ...
     */
    private function calculateCommission($bet, $betUser)
    {
        $betAmount = $bet->amount;
        
        // Get all active commission rates
        $commissionRates = \App\Models\CommissionRate::getActiveRates();
        
        if ($commissionRates->isEmpty()) {
            return; // No commission rates configured
        }
        
        // Get referral chain (F1, F2, F3...)
        // F1 = người giới thiệu user bet (user bet được giới thiệu bởi F1)
        // F2 = người giới thiệu F1
        // F3 = người giới thiệu F2
        // ...
        $currentUser = $betUser;
        $level = 1;
        
        while ($currentUser && $currentUser->referred_by) {
            $referrer = $currentUser->referrer;
            if (!$referrer) {
                break;
            }
            
            // Find commission rate for this level (F1, F2, F3...)
            $levelKey = 'F' . $level;
            $rate = $commissionRates->firstWhere('level', $levelKey);
            
            if ($rate && $rate->rate > 0) {
                // Calculate commission amount based on bet amount
                $commissionAmount = ($betAmount * $rate->rate) / 100;
                
                // Create commission record with status 'pending'
                // Commission sẽ chuyển sang 'available' sau khi bet kết thúc (round finished)
                \App\Models\UserCommission::create([
                    'user_id' => $referrer->id, // User nhận hoa hồng (người giới thiệu)
                    'from_user_id' => $betUser->id, // User đặt cược
                    'bet_id' => $bet->id,
                    'level' => $levelKey,
                    'bet_amount' => $betAmount,
                    'commission_rate' => $rate->rate,
                    'commission_amount' => $commissionAmount,
                    'status' => 'pending', // Chờ bet kết thúc mới có thể rút
                ]);
            }
            
            // Move to next level
            $currentUser = $referrer;
            $level++;
            
            // Stop if we've processed all configured levels
            if ($level > $commissionRates->count()) {
                break;
            }
        }
    }

    /**
     * Get gem types with rates
     */
    public function getGemTypes()
    {
        $payoutRates = $this->getPayoutRates();
        $gemTypes = [];
        foreach (self::GEM_RANDOM_RATES as $type => $rate) {
            $gemTypes[] = [
                'type' => $type,
                'random_rate' => $rate,
                'payout_rate' => $payoutRates[$type],
            ];
        }
        return $gemTypes;
    }

    /**
     * Random a gem type based on rates
     */
    public static function randomGemType()
    {
        $rand = mt_rand(1, 100);
        $cumulative = 0;
        
        foreach (self::GEM_RANDOM_RATES as $type => $rate) {
            $cumulative += $rate;
            if ($rand <= $cumulative) {
                return $type;
            }
        }
        
        // Fallback (should never reach here)
        return 'thachanh';
    }
}
