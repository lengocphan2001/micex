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
        // Calculate previous round time (30 seconds per round)
        $now = now()->timestamp;
        $currentRoundTime = floor($now / 30) * 30;
        $previousRoundTime = $currentRoundTime - 30;
        
        // Get all pending bets for previous round
        $pendingBets = TradingBet::where('symbol', $symbol)
            ->where('round_time', $previousRoundTime)
            ->where('status', 'pending')
            ->get();
        
        if ($pendingBets->isEmpty()) {
            return;
        }
        
        // Get exit price from cache (updated by PriceTick command)
        $exitPrice = (float)\Illuminate\Support\Facades\Cache::get(
            "{$symbol}_PRICE",
            $this->getDefaultPrice($symbol)
        );
        
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
                    
                    // Log for debugging
                    $this->info("Processed bet #{$bet->id}: {$bet->direction} - " . ($isWin ? 'WON' : 'LOST') . " - Profit: {$profit}");
                    
                } catch (\Exception $e) {
                    Log::error('Error processing trading bet', [
                        'bet_id' => $bet->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
    
    private function getDefaultPrice(string $symbol): float
    {
        return match($symbol) {
            'BTCUSDT' => 94000,
            'ETHUSDT' => 3500,
            'BNBUSDT' => 600,
            'SOLUSDT' => 100,
            default => 1000,
        };
    }
}
