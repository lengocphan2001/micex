<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuturesPosition extends Model
{
    protected $fillable = [
        'user_id',
        'symbol',
        'side',
        'entry_price',
        'size',
        'leverage',
        'margin',
        'pnl',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'entry_price' => 'decimal:8',
        'size' => 'decimal:8',
        'leverage' => 'integer',
        'margin' => 'decimal:8',
        'pnl' => 'decimal:8',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate PnL for this position
     */
    public function calculatePnL(float $currentPrice): float
    {
        $priceDiff = $currentPrice - $this->entry_price;
        
        if ($this->side === 'long') {
            $pnl = $priceDiff * $this->size;
        } else {
            $pnl = -$priceDiff * $this->size;
        }

        return $pnl;
    }

    /**
     * Check if position should be liquidated
     */
    public function shouldLiquidate(float $currentPrice): bool
    {
        $pnl = $this->calculatePnL($currentPrice);
        return ($this->margin + $pnl) <= 0;
    }

    /**
     * Liquidate position
     */
    public function liquidate(): void
    {
        $this->update([
            'status' => 'liquidated',
            'closed_at' => now(),
            'pnl' => -$this->margin, // Total loss = margin
        ]);
    }
}
