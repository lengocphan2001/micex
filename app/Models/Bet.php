<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'round_id',
        'user_id',
        'gem_type',
        'bet_type',
        'bet_value',
        'amount',
        'amount_from_deposit',
        'amount_from_reward',
        'payout_rate',
        'status',
        'payout_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_from_deposit' => 'decimal:2',
        'amount_from_reward' => 'decimal:2',
        'payout_rate' => 'decimal:2',
        'payout_amount' => 'decimal:2',
    ];

    /**
     * Relationship: Round
     */
    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Commissions generated from this bet
     */
    public function commissions()
    {
        return $this->hasMany(UserCommission::class);
    }
}
