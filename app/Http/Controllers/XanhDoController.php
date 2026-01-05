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

        DB::beginTransaction();
        try {
            $user = User::where('id', $user->id)->lockForUpdate()->first();

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

            if ($user->balance < $validated['amount']) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Số dư không đủ để đặt cược.',
                ], 400);
            }

            $user->balance -= $validated['amount'];
            $user->betting_requirement = max(0, ($user->betting_requirement ?? 0) - $validated['amount']);
            $user->save();

            $bet = Bet::create([
                'round_id' => $round->id,
                'user_id' => $user->id,
                'gem_type' => $validated['gem_type'],
                'amount' => $validated['amount'],
                'payout_rate' => $payoutRates[$validated['gem_type']] ?? 1.95,
                'status' => 'pending',
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
     * Get user's bet for current XanhDo round + balance
     */
    public function getMyBet()
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->refresh();
        $round = Round::getCurrentRound(self::GAME_KEY);

        $bet = null;
        if ($round) {
            $bet = Bet::where('round_id', $round->id)
                ->where('user_id', $user->id)
                ->with('round')
                ->first();
        }

        if (!$bet) {
            // fallback: last bet in this game (join rounds)
            $bet = Bet::where('user_id', $user->id)
                ->whereHas('round', function ($q) {
                    $q->where('game_key', self::GAME_KEY);
                })
                ->with('round')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$bet) {
            return response()->json([
                'bet' => null,
                'balance' => $user->balance,
            ]);
        }

        return response()->json([
            'bet' => [
                'id' => $bet->id,
                'round_id' => $bet->round_id,
                'round_number' => $bet->round->round_number ?? null,
                'gem_type' => $bet->gem_type,
                'amount' => $bet->amount,
                'payout_rate' => $bet->payout_rate,
                'status' => $bet->status,
                'payout_amount' => $bet->payout_amount,
                'round' => [
                    'id' => $bet->round->id ?? null,
                    'round_number' => $bet->round->round_number ?? null,
                    'final_result' => $bet->round->final_result ?? null,
                    'admin_set_result' => $bet->round->admin_set_result ?? null,
                ],
            ],
            'balance' => $user->balance,
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

        if ($round->status === 'running' && !$round->final_result) {
            $round->refresh();
            $finalResult = $round->admin_set_result ?: $round->randomResultBasedOnBets();
            $round->finish($finalResult);
            $round->refresh();
        }

        $result = $round->admin_set_result ?: $round->final_result;

        // Backward compatibility map
        $resultMap = [
            'thachanh' => 'kcxanh',
            'kimcuong' => 'kcdo',
        ];
        $result = $resultMap[$result] ?? $result;

        return response()->json([
            'round_number' => $round->round_number,
            'result' => $result,
            'admin_set_result' => $round->admin_set_result,
            'final_result' => $round->final_result,
        ]);
    }
}


