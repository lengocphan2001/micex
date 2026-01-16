<?php

namespace App\Console\Commands;

use App\Models\TradingBet;
use App\Models\Candle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessTradingBets extends Command
{
    protected $signature = 'trading:process-bets';
    protected $description = 'Process trading bets when candle closes (Futures Engine)';

    public function handle()
    {
        $symbols = ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT'];
        
        foreach ($symbols as $symbol) {
            $this->processBetsForSymbol($symbol);
        }
    }

    private function processBetsForSymbol(string $symbol)
    {
        // Get the most recent closed 1m candle
        $lastCandle = Candle::where('symbol', $symbol)
            ->where('timeframe', '1m')
            ->orderBy('time', 'desc')
            ->first();
        
        if (!$lastCandle) {
            return;
        }
        
        // Get all pending bets for this round_time
        $pendingBets = TradingBet::where('symbol', $symbol)
            ->where('round_time', $lastCandle->time)
            ->where('status', 'pending')
            ->get();
        
        if ($pendingBets->isEmpty()) {
            return;
        }
        
        $exitPrice = (float)$lastCandle->close;
        
        DB::transaction(function () use ($pendingBets, $exitPrice) {
            foreach ($pendingBets as $bet) {
                try {
                    $entryPrice = (float)$bet->entry_price;
                    $priceChange = $exitPrice - $entryPrice;
                    
                    // Determine if bet wins
                    $isWin = false;
                    if ($bet->direction === 'up') {
                        $isWin = $priceChange > 0; // Win if price goes up
                    } else {
                        $isWin = $priceChange < 0; // Win if price goes down
                    }
                    
                    // Calculate profit
                    if ($isWin) {
                        $profit = $bet->amount * ($bet->payout_rate - 1); // Profit = amount * (payout_rate - 1)
                        $bet->status = 'won';
                    } else {
                        $profit = -$bet->amount; // Lose entire bet amount
                        $bet->status = 'lost';
                    }
                    
                    $bet->exit_price = $exitPrice;
                    $bet->profit = $profit;
                    $bet->result_at = now();
                    $bet->save();
                    
                    // Update user balance
                    $user = $bet->user;
                    if ($user) {
                        if ($isWin) {
                            // Return bet amount + profit
                            $user->balance += $bet->amount + $profit;
                        }
                        // If lost, balance was already deducted when bet was placed
                        $user->save();
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Error processing trading bet', [
                        'bet_id' => $bet->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}
