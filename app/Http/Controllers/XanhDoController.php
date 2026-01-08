<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\Round;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class XanhDoController extends Controller
{
    private const GAME_KEY = 'xanhdo';

    /**
     * Get gem types with rates (reuse same settings as explore for now)
     */
    public function getGemTypes()
    {
        // Use the same payout rates as ExploreController (shared config)
        $payoutRates = [
            'kcxanh' => (float) SystemSetting::getValue('gem_payout_rate_kcxanh', SystemSetting::getValue('gem_payout_rate_thachanh', 1.95)),
            'daquy' => (float) SystemSetting::getValue('gem_payout_rate_daquy', 5.95),
            'kcdo' => (float) SystemSetting::getValue('gem_payout_rate_kcdo', SystemSetting::getValue('gem_payout_rate_kimcuong', 1.95)),
        ];

        // Calculate random rates from payout rates (inverse relationship)
        $inverseValues = [];
        $totalInverse = 0;
        foreach ($payoutRates as $type => $payoutRate) {
            $inverseValues[$type] = 1 / max(0.0001, $payoutRate);
            $totalInverse += $inverseValues[$type];
        }
        $randomRates = [];
        foreach ($inverseValues as $type => $inverseValue) {
            $randomRates[$type] = round(($inverseValue / $totalInverse) * 100, 2);
        }
        $sum = array_sum($randomRates);
        if ($sum != 100) {
            $diff = 100 - $sum;
            $firstType = array_key_first($randomRates);
            $randomRates[$firstType] += $diff;
        }

        $gemTypes = [];
        foreach ($payoutRates as $type => $payoutRate) {
            $gemTypes[] = [
                'type' => $type,
                'random_rate' => $randomRates[$type] ?? 33.33,
                'payout_rate' => $payoutRate,
            ];
        }

        return response()->json($gemTypes);
    }

    /**
     * Place a bet (scoped to XanhDo game rounds)
     */
    public function placeBet(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'gem_type' => 'required|in:kcxanh,daquy,kcdo',
            'amount' => 'required|numeric|min:0.01',
            'bet_type' => 'nullable|in:number,color',
            'bet_value' => 'nullable|string|max:10', // For number bets: '0'-'9'
        ]);

        $round = Round::getCurrentRound(self::GAME_KEY);
        if (!$round) {
            return response()->json(['error' => 'Round not found'], 404);
        }

        $currentSecond = 0;
        if ($round->status === 'running' && $round->started_at) {
            $elapsedSeconds = now()->diffInSeconds($round->started_at);
            $currentSecond = min(60, $elapsedSeconds + 1);
        }

        if ($round->status !== 'running' || $currentSecond > 30) {
            return response()->json([
                'error' => 'Thời gian đặt cược đã kết thúc. Chỉ có thể đặt cược trong 30 giây đầu của mỗi phiên.',
            ], 400);
        }

        if ($user->balance < $validated['amount']) {
            return response()->json([
                'error' => 'Số dư không đủ để đặt cược.',
            ], 400);
        }

        $payoutRates = [
            'kcxanh' => (float) SystemSetting::getValue('gem_payout_rate_kcxanh', SystemSetting::getValue('gem_payout_rate_thachanh', 1.95)),
            'daquy' => (float) SystemSetting::getValue('gem_payout_rate_daquy', 5.95),
            'kcdo' => (float) SystemSetting::getValue('gem_payout_rate_kcdo', SystemSetting::getValue('gem_payout_rate_kimcuong', 1.95)),
        ];
        
        // Number bets have fixed payout rate of 9.75
        $numberBetPayoutRate = 9.75;

        DB::beginTransaction();
        try {
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            // Allow multiple bets with same gem_type in xanhdo game
            // Each selection (number or color) is a separate bet
            // No need to check for duplicate gem_type

            // Check total balance (deposit + reward)
            $totalBalance = $user->getTotalBalance();
            if ($totalBalance < $validated['amount']) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Số dư không đủ để đặt cược.',
                ], 400);
            }

            // Deduct from wallets (ưu tiên ví nạp trước)
            $deduction = $user->deductFromWallets($validated['amount']);
            $user->betting_requirement = max(0, ($user->betting_requirement ?? 0) - $validated['amount']);
            $user->save();

            // Determine payout rate: number bets = 9.75, color bets = color rate
            $payoutRate = ($validated['bet_type'] === 'number') 
                ? $numberBetPayoutRate 
                : ($payoutRates[$validated['gem_type']] ?? 1.95);

            $bet = Bet::create([
                'round_id' => $round->id,
                'user_id' => $user->id,
                'gem_type' => $validated['gem_type'],
                'bet_type' => $validated['bet_type'] ?? null,
                'bet_value' => $validated['bet_value'] ?? null,
                'amount' => $validated['amount'],
                'payout_rate' => $payoutRate,
                'status' => 'pending',
                'amount_from_deposit' => $deduction['from_deposit'] ?? 0,
                'amount_from_reward' => $deduction['from_reward'] ?? 0,
            ]);

            DB::commit();
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Đặt cược thành công!',
                'betting_requirement' => $user->betting_requirement ?? 0,
                'bet' => [
                    'id' => $bet->id,
                    'gem_type' => $bet->gem_type,
                    'amount' => $bet->amount,
                    'payout_rate' => $bet->payout_rate,
                ],
                'balance' => $user->balance,
                'reward_balance' => $user->reward_balance,
                'total_balance' => $user->getTotalBalance(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Có lỗi xảy ra khi đặt cược: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's bets for current XanhDo round + balance
     */
    public function getMyBet()
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->refresh();
        $round = Round::getCurrentRound(self::GAME_KEY);

        $bets = [];
        if ($round) {
            $bets = Bet::where('round_id', $round->id)
                ->where('user_id', $user->id)
                ->with('round')
                ->get()
                ->map(function ($bet) {
                    return [
                        'id' => $bet->id,
                        'round_id' => $bet->round_id,
                        'round_number' => $bet->round->round_number ?? null,
                        'gem_type' => $bet->gem_type,
                        'amount' => $bet->amount,
                        'payout_rate' => $bet->payout_rate,
                        'status' => $bet->status,
                        'payout_amount' => $bet->payout_amount,
                    ];
                })
                ->toArray();
        }

        return response()->json([
            'bets' => $bets,
            'balance' => $user->balance,
            'reward_balance' => $user->reward_balance ?? 0,
            'total_balance' => $user->getTotalBalance(),
        ]);
    }

    /**
     * Get round result for XanhDo (finish round if needed)
     */
    public function getRoundResult(Request $request)
    {
        $validated = $request->validate([
            'round_number' => 'required|integer',
        ]);

        $round = Round::where('game_key', self::GAME_KEY)
            ->where('round_number', $validated['round_number'])
            ->where('seed', self::GAME_KEY . '_round_' . $validated['round_number'])
            ->first();

        if (!$round) {
            return response()->json([
                'result' => null,
                'message' => 'Round not found',
            ], 404);
        }

        // NOTE: final_result can be string "0" (valid) => do NOT use truthy checks
        if ($round->status === 'running' && ($round->final_result === null || $round->final_result === '')) {
            $round->refresh();
            $finalResult = ($round->admin_set_result !== null && $round->admin_set_result !== '')
                ? $round->admin_set_result
                : $round->randomResultBasedOnBets();
            $round->finish($finalResult);
            $round->refresh();
        }

        // For finished rounds, final_result is the source of truth.
        // For running rounds, surface admin_set_result if set (including "0"), otherwise null.
        $result = ($round->final_result !== null && $round->final_result !== '')
            ? $round->final_result
            : (($round->admin_set_result !== null && $round->admin_set_result !== '') ? $round->admin_set_result : null);

        // For xanhdo, result is a number (0-9), return as-is
        // No backward compatibility mapping needed for xanhdo

        return response()->json([
            'round_number' => $round->round_number,
            'result' => $result,
            'admin_set_result' => $round->admin_set_result,
            'final_result' => $round->final_result,
        ]);
    }

    /**
     * Get recent 10 round results for XanhDo
     */
    public function getRecentResults()
    {
        $rounds = Round::where('game_key', self::GAME_KEY)
            ->whereNotNull('final_result')
            ->orderBy('round_number', 'desc')
            ->limit(10)
            ->get(['id', 'round_number', 'final_result', 'admin_set_result', 'ended_at', 'created_at'])
            ->map(function ($round) {
                // IMPORTANT:
                // For finished rounds, always use final_result as the source of truth.
                // admin_set_result can be set but not applied (race timing) so it may differ.
                $result = $round->final_result;
                $resultNum = is_numeric($result) ? (int) $result : null;
                
                // Determine winning colors based on number
                $winningColors = [];
                if ($resultNum !== null && $resultNum >= 0 && $resultNum <= 9) {
                    if ($resultNum === 0) {
                        $winningColors = ['daquy', 'kcdo']; // tím + đỏ
                    } elseif ($resultNum === 5) {
                        $winningColors = ['daquy', 'kcxanh']; // tím + xanh
                    } elseif (in_array($resultNum, [1, 3, 7, 9])) {
                        $winningColors = ['kcxanh']; // xanh
                    } elseif (in_array($resultNum, [2, 4, 6, 8])) {
                        $winningColors = ['kcdo']; // đỏ
                    }
                }
                
                // Format time
                $time = $round->ended_at ? $round->ended_at->format('H:i:s') : ($round->created_at ? $round->created_at->format('H:i:s') : '');
                
                return [
                    'round_number' => $round->round_number,
                    'result' => $resultNum !== null ? $resultNum : $result,
                    'winning_colors' => $winningColors,
                    'time' => $time,
                    'admin_set_result' => $round->admin_set_result,
                    'final_result' => $round->final_result,
                ];
            })
            ->values();

        return response()->json($rounds);
    }

    /**
     * Get total winnings for a specific round
     */
    public function getRoundWinnings(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'round_number' => 'required|integer',
        ]);

        $round = Round::where('game_key', self::GAME_KEY)
            ->where('round_number', $validated['round_number'])
            ->first();

        if (!$round) {
            return response()->json([
                'total_winnings' => 0,
                'has_winnings' => false,
            ]);
        }

        // Get all winning bets for this round and user
        $winningBets = Bet::where('round_id', $round->id)
            ->where('user_id', $user->id)
            ->where('status', 'won')
            ->get();

        $totalWinnings = $winningBets->sum('payout_amount');

        return response()->json([
            'total_winnings' => $totalWinnings,
            'has_winnings' => $totalWinnings > 0,
            'round_number' => $round->round_number,
        ]);
    }
}


