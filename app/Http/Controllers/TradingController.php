<?php

namespace App\Http\Controllers;

use App\Models\Round;
use App\Models\Bet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TradingController extends Controller
{
    const GAME_KEY = 'trading';
    const BETTING_WINDOW_SECONDS = 55; // 55 giây đầu có thể đặt cược

    /**
     * Place a trading bet (BUY or SELL)
     */
    public function placeBet(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'direction' => 'required|in:BUY,SELL',
            'amount' => 'required|numeric|min:0.01',
            'wallet_type' => 'nullable|in:deposit,reward',
        ]);

        // Get current round
        $round = Round::getCurrentRound(self::GAME_KEY);
        if (!$round) {
            return response()->json(['error' => 'Round not found'], 404);
        }

        // Calculate current second
        $currentSecond = 0;
        if ($round->status === 'running' && $round->started_at) {
            $elapsedSeconds = now()->diffInSeconds($round->started_at);
            $currentSecond = min(60, $elapsedSeconds + 1);
        }

        // Check if round is still accepting bets (only in first 55 seconds)
        if ($round->status !== 'running' || $currentSecond > self::BETTING_WINDOW_SECONDS) {
            return response()->json([
                'error' => 'Thời gian đặt cược đã kết thúc. Chỉ có thể đặt cược trong 55 giây đầu của mỗi phiên.',
            ], 400);
        }

        // Determine wallet type (default to deposit if not specified)
        $walletType = $validated['wallet_type'] ?? 'deposit';
        
        // Check balance for selected wallet
        if ($walletType === 'reward') {
            $walletBalance = $user->reward_balance ?? 0;
            if ($walletBalance < $validated['amount']) {
                return response()->json([
                    'error' => 'Số dư ví thưởng không đủ để đặt cược.',
                ], 400);
            }
        } else {
            $walletBalance = $user->balance ?? 0;
            if ($walletBalance < $validated['amount']) {
                return response()->json([
                    'error' => 'Số dư ví nạp không đủ để đặt cược.',
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            // Lock user to prevent concurrent balance updates
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            // Check balance again after lock
            if ($walletType === 'reward') {
                $walletBalance = $user->reward_balance ?? 0;
                if ($walletBalance < $validated['amount']) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Số dư ví thưởng không đủ để đặt cược.',
                    ], 400);
                }
            } else {
                $walletBalance = $user->balance ?? 0;
                if ($walletBalance < $validated['amount']) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Số dư ví nạp không đủ để đặt cược.',
                    ], 400);
                }
            }

            // Deduct from specific wallet
            try {
                $deduction = $user->deductFromSpecificWallet($validated['amount'], $walletType);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'error' => $e->getMessage(),
                ], 400);
            }
            
            // Decrease betting requirement
            $user->betting_requirement = max(0, ($user->betting_requirement ?? 0) - $validated['amount']);
            $user->save();

            // Create bet
            $bet = Bet::create([
                'round_id' => $round->id,
                'user_id' => $user->id,
                'gem_type' => 'kcxanh', // Dummy value for trading
                'bet_direction' => $validated['direction'],
                'amount' => $validated['amount'],
                'matched_amount' => 0,
                'pending_amount' => $validated['amount'],
                'payout_rate' => 1.95, // Dummy value for trading
                'status' => 'pending',
                'amount_from_deposit' => $deduction['from_deposit'] ?? 0,
                'amount_from_reward' => $deduction['from_reward'] ?? 0,
            ]);

            // Try to match with opposite direction orders
            $this->matchBet($bet);

            DB::commit();

            $user->refresh();
            $bet->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Đặt cược thành công!',
                'bet' => [
                    'id' => $bet->id,
                    'direction' => $bet->bet_direction,
                    'amount' => $bet->amount,
                    'matched_amount' => $bet->matched_amount,
                    'pending_amount' => $bet->pending_amount,
                ],
                'balance' => $user->balance,
                'reward_balance' => $user->reward_balance ?? 0,
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
     * Match a bet with opposite direction orders (FIFO)
     */
    private function matchBet(Bet $newBet)
    {
        $oppositeDirection = $newBet->bet_direction === 'BUY' ? 'SELL' : 'BUY';
        $remainingAmount = $newBet->pending_amount ?? $newBet->amount;

        if ($remainingAmount <= 0) {
            return;
        }

        // Get pending bets of opposite direction, ordered by created_at (FIFO)
        // Exclude the current bet itself (in case of re-matching)
        $oppositeBets = Bet::where('round_id', $newBet->round_id)
            ->where('bet_direction', $oppositeDirection)
            ->where('status', 'pending')
            ->where('id', '!=', $newBet->id)
            ->where(function($query) {
                $query->where(function($q) {
                    $q->where('pending_amount', '>', 0)
                      ->orWhereNull('pending_amount');
                })
                ->orWhereRaw('matched_amount < amount');
            })
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($oppositeBets as $oppositeBet) {
            if ($remainingAmount <= 0) {
                break;
            }

            // Calculate available amount to match for opposite bet
            $oppositeMatched = $oppositeBet->matched_amount ?? 0;
            $oppositePending = $oppositeBet->pending_amount;
            
            // If pending_amount is NULL, calculate from amount - matched_amount
            if ($oppositePending === null) {
                $oppositePending = $oppositeBet->amount - $oppositeMatched;
            }
            
            if ($oppositePending <= 0) {
                continue;
            }

            // Match amount
            $matchAmount = min($remainingAmount, $oppositePending);

            // Update new bet
            $newBet->matched_amount = ($newBet->matched_amount ?? 0) + $matchAmount;
            $newBet->pending_amount = ($newBet->pending_amount ?? $newBet->amount) - $matchAmount;
            $newBet->save();

            // Update opposite bet
            $oppositeBet->matched_amount = ($oppositeBet->matched_amount ?? 0) + $matchAmount;
            $oppositeBet->pending_amount = ($oppositeBet->amount - ($oppositeBet->matched_amount ?? 0));
            $oppositeBet->save();

            $remainingAmount -= $matchAmount;
        }

        // Final update: ensure pending_amount is correct
        if ($remainingAmount >= 0) {
            $newBet->pending_amount = $remainingAmount;
            $newBet->save();
        }
    }

    /**
     * Get user's trading bets for current round
     */
    public function getMyBets()
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $round = Round::getCurrentRound(self::GAME_KEY);
        
        if (!$round) {
            return response()->json([
                'bets' => [],
                'balance' => $user->balance,
                'reward_balance' => $user->reward_balance ?? 0,
                'total_balance' => $user->getTotalBalance(),
            ]);
        }

        $bets = Bet::where('round_id', $round->id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $betData = $bets->map(function($bet) {
            return [
                'id' => $bet->id,
                'direction' => $bet->bet_direction,
                'amount' => $bet->amount,
                'matched_amount' => $bet->matched_amount ?? 0,
                'pending_amount' => $bet->pending_amount ?? ($bet->amount - ($bet->matched_amount ?? 0)),
                'status' => $bet->status,
                'payout_amount' => $bet->payout_amount,
                'payout_rate' => $bet->payout_rate,
            ];
        });

        return response()->json([
            'bets' => $betData,
            'balance' => $user->balance,
            'reward_balance' => $user->reward_balance ?? 0,
            'total_balance' => $user->getTotalBalance(),
        ]);
    }

    /**
     * Get current round status
     */
    public function getCurrentRound()
    {
        $round = Round::getCurrentRound(self::GAME_KEY);
        
        if (!$round) {
            return response()->json([
                'round' => null,
                'message' => 'Round not found',
            ]);
        }

        $currentSecond = 0;
        $phase = 'betting';
        
        if ($round->status === 'running' && $round->started_at) {
            $elapsedSeconds = now()->diffInSeconds($round->started_at);
            $currentSecond = min(60, $elapsedSeconds + 1);
            
            if ($currentSecond <= self::BETTING_WINDOW_SECONDS) {
                $phase = 'betting';
            } else {
                $phase = 'result';
            }
        } elseif ($round->status === 'finished') {
            $phase = 'result';
        }

        // Get total BUY and SELL amounts
        $buyAmount = Bet::where('round_id', $round->id)
            ->where('bet_direction', 'BUY')
            ->where('status', 'pending')
            ->sum('amount') ?? 0;

        $sellAmount = Bet::where('round_id', $round->id)
            ->where('bet_direction', 'SELL')
            ->where('status', 'pending')
            ->sum('amount') ?? 0;

        $totalAmount = $buyAmount + $sellAmount;
        $buyPercentage = $totalAmount > 0 ? round(($buyAmount / $totalAmount) * 100) : 0;
        $sellPercentage = $totalAmount > 0 ? round(($sellAmount / $totalAmount) * 100) : 0;

        return response()->json([
            'round' => [
                'id' => $round->id,
                'round_number' => $round->round_number,
                'status' => $round->status,
                'phase' => $phase,
                'current_second' => $currentSecond,
                'final_result' => $round->final_result,
                'started_at' => $round->started_at?->toIso8601String(),
            ],
            'statistics' => [
                'buy_amount' => $buyAmount,
                'sell_amount' => $sellAmount,
                'buy_percentage' => $buyPercentage,
                'sell_percentage' => $sellPercentage,
            ],
        ]);
    }

    /**
     * Get round winnings for trading game
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
        // For trading, only matched_amount contributes to winnings (pending_amount was refunded)
        $winningBets = Bet::where('round_id', $round->id)
            ->where('user_id', $user->id)
            ->where('status', 'won')
            ->get();

        $totalWinnings = $winningBets->sum('payout_amount');
        
        \Log::info('Trading round winnings check', [
            'round_id' => $round->id,
            'round_number' => $round->round_number,
            'user_id' => $user->id,
            'winning_bets_count' => $winningBets->count(),
            'total_winnings' => $totalWinnings,
            'bets' => $winningBets->map(function($bet) {
                return [
                    'id' => $bet->id,
                    'status' => $bet->status,
                    'payout_amount' => $bet->payout_amount,
                ];
            })->toArray(),
        ]);

        return response()->json([
            'total_winnings' => $totalWinnings,
            'has_winnings' => $totalWinnings > 0,
            'round_number' => $round->round_number,
        ]);
    }

    /**
     * Process refunds for unmatched bets when betting window closes (55 seconds)
     */
    public static function processRefundsForBettingWindow($roundId)
    {
        $round = Round::find($roundId);
        if (!$round || $round->game_key !== self::GAME_KEY) {
            \Log::warning("Cannot process refunds: round not found or wrong game", ['round_id' => $roundId]);
            return;
        }

        DB::beginTransaction();
        try {
            // Get all pending bets with pending_amount > 0 that haven't been refunded yet
            $unmatchedBets = Bet::where('round_id', $round->id)
                ->where('status', 'pending')
                ->where(function($query) {
                    $query->where('pending_amount', '>', 0)
                          ->orWhereRaw('(pending_amount IS NULL AND matched_amount < amount)');
                })
                ->lockForUpdate()
                ->get();

            \Log::info("Processing betting window refunds", [
                'round_id' => $round->id,
                'round_number' => $round->round_number,
                'unmatched_bets_count' => $unmatchedBets->count(),
            ]);

            $totalRefunded = 0;
            foreach ($unmatchedBets as $bet) {
                $pendingAmount = $bet->pending_amount ?? ($bet->amount - ($bet->matched_amount ?? 0));
                
                if ($pendingAmount <= 0) {
                    continue;
                }

                // Refund to user
                $user = User::where('id', $bet->user_id)->lockForUpdate()->first();
                if ($user) {
                    // Refund proportionally from deposit and reward wallets
                    $depositRatio = $bet->amount > 0 ? ($bet->amount_from_deposit / $bet->amount) : 0;
                    $rewardRatio = $bet->amount > 0 ? ($bet->amount_from_reward / $bet->amount) : 0;

                    $refundDeposit = $pendingAmount * $depositRatio;
                    $refundReward = $pendingAmount * $rewardRatio;

                    $oldBalance = $user->balance;
                    $oldRewardBalance = $user->reward_balance ?? 0;

                    $user->balance += $refundDeposit;
                    $user->reward_balance = ($user->reward_balance ?? 0) + $refundReward;
                    $user->save();
                    
                    $totalRefunded += $pendingAmount;
                    
                    \Log::info("Trading betting window refund processed", [
                        'bet_id' => $bet->id,
                        'user_id' => $user->id,
                        'pending_amount' => $pendingAmount,
                        'refund_deposit' => $refundDeposit,
                        'refund_reward' => $refundReward,
                        'old_balance' => $oldBalance,
                        'new_balance' => $user->balance,
                        'old_reward_balance' => $oldRewardBalance,
                        'new_reward_balance' => $user->reward_balance,
                    ]);
                }

                // Update bet: set pending_amount to 0
                $bet->pending_amount = 0;
                $bet->save();
            }

            DB::commit();
            
            \Log::info("Betting window refunds completed", [
                'round_id' => $round->id,
                'round_number' => $round->round_number,
                'total_refunded' => $totalRefunded,
                'bets_refunded' => $unmatchedBets->count(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error processing betting window refunds for round {$roundId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Process refunds for unmatched bets when round ends
     */
    public static function processRefunds($roundId)
    {
        $round = Round::find($roundId);
        if (!$round || $round->game_key !== self::GAME_KEY) {
            return;
        }

        DB::beginTransaction();
        try {
            // Get all pending bets with pending_amount > 0
            $unmatchedBets = Bet::where('round_id', $round->id)
                ->where('status', 'pending')
                ->where(function($query) {
                    $query->where('pending_amount', '>', 0)
                          ->orWhereRaw('(pending_amount IS NULL AND matched_amount < amount)');
                })
                ->lockForUpdate()
                ->get();

            foreach ($unmatchedBets as $bet) {
                $pendingAmount = $bet->pending_amount ?? ($bet->amount - ($bet->matched_amount ?? 0));
                
                if ($pendingAmount <= 0) {
                    continue;
                }

                // Refund to user
                $user = User::where('id', $bet->user_id)->lockForUpdate()->first();
                if ($user) {
                    // Refund proportionally from deposit and reward wallets
                    $depositRatio = $bet->amount > 0 ? ($bet->amount_from_deposit / $bet->amount) : 0;
                    $rewardRatio = $bet->amount > 0 ? ($bet->amount_from_reward / $bet->amount) : 0;

                    $refundDeposit = $pendingAmount * $depositRatio;
                    $refundReward = $pendingAmount * $rewardRatio;

                    $user->balance += $refundDeposit;
                    $user->reward_balance = ($user->reward_balance ?? 0) + $refundReward;
                    $user->save();
                }

                // Update bet: set pending_amount to 0
                $bet->pending_amount = 0;
                $bet->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error processing refunds for round {$roundId}: " . $e->getMessage());
        }
    }
}
