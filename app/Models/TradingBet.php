<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradingBet extends Model
{
    protected $fillable = [
        'user_id',
        'symbol',
        'direction',
        'amount',
        'payout_rate',
        'entry_price',
        'exit_price',
        'status',
        'profit',
        'round_time',
        'placed_at',
        'result_at',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'payout_rate' => 'decimal:2',
        'entry_price' => 'decimal:8',
        'exit_price' => 'decimal:8',
        'profit' => 'decimal:8',
        'round_time' => 'integer',
        'placed_at' => 'datetime',
        'result_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate profit based on result
     */
    public function calculateProfit(float $exitPrice): float
    {
        $priceChange = $exitPrice - $this->entry_price;
        
        if ($this->direction === 'up') {
            // Bet on UP: win if price goes up
            $isWin = $priceChange > 0;
        } else {
            // Bet on DOWN: win if price goes down
            $isWin = $priceChange < 0;
        }
        
        if ($isWin) {
            return $this->amount * ($this->payout_rate - 1); // Profit = amount * (payout_rate - 1)
        }
        
        return -$this->amount; // Lose = lose entire bet amount
    }
}
