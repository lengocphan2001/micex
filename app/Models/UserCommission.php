<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCommission extends Model
{
    protected $fillable = [
        'user_id',
        'from_user_id',
        'bet_id',
        'level',
        'bet_amount',
        'commission_rate',
        'commission_amount',
        'status',
        'withdrawn_at',
    ];

    protected $casts = [
        'bet_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'withdrawn_at' => 'datetime',
    ];

    /**
     * Relationship: User who receives commission
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: User who generated commission (bet and won)
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Relationship: Bet that generated commission
     */
    public function bet(): BelongsTo
    {
        return $this->belongsTo(Bet::class);
    }

    /**
     * Get total available commission for a user
     */
    public static function getAvailableCommission($userId)
    {
        return self::where('user_id', $userId)
            ->where('status', 'available')
            ->sum('commission_amount');
    }

    /**
     * Withdraw commission (mark as withdrawn)
     */
    public function withdraw()
    {
        $this->update([
            'status' => 'withdrawn',
            'withdrawn_at' => now(),
        ]);
    }
}
