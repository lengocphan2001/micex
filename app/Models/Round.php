<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'round_number',
        'seed',
        'status',
        'current_second',
        'current_result',
        'final_result',
        'admin_set_result',
        'results',
        'started_at',
        'ended_at',
        'break_until',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'break_until' => 'datetime',
        'results' => 'array', // JSON array of results
    ];

    /**
     * Relationship: Bets
     */
    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    /**
     * Get current active round or create new one
     */
    public static function getCurrentRound()
    {
        // Get the latest round
        $round = self::orderBy('id', 'desc')->first();
        
        // Check if we need to create a new round
        $shouldCreateNew = false;
        
        if (!$round) {
            $shouldCreateNew = true;
        } elseif ($round->status === 'finished') {
            // Check if break time has passed
            if ($round->break_until && now()->lt($round->break_until)) {
                // Still in break time, return the finished round
                return $round;
            }
            $shouldCreateNew = true;
        }
        
        if ($shouldCreateNew) {
            $roundNumber = self::max('round_number') ?? 0;
            // Generate a unique seed for this round
            $seed = uniqid('round_', true) . '_' . time();
            $round = self::create([
                'round_number' => $roundNumber + 1,
                'seed' => $seed,
                'status' => 'pending',
                'current_second' => 0,
            ]);
        }
        
        return $round;
    }

    /**
     * Start the round
     */
    public function start()
    {
        // Random first result
        $firstResult = \App\Http\Controllers\ExploreController::randomGemType();
        
        $this->update([
            'status' => 'running',
            'started_at' => now(),
            'current_second' => 1,
            'current_result' => $firstResult,
        ]);
    }

    /**
     * Update current second and result
     */
    public function updateSecond($second, $result)
    {
        $this->update([
            'current_second' => $second,
            'current_result' => $result,
        ]);
        
        // If it's the last second (60), set final result and finish
        if ($second >= 60) {
            $this->finish($result);
        }
    }

    /**
     * Finish the round
     */
    public function finish($finalResult)
    {
        // Set break time: 1 minute after round ends
        $breakUntil = now()->addMinute();
        
        $this->update([
            'status' => 'finished',
            'final_result' => $finalResult,
            'ended_at' => now(),
            'break_until' => $breakUntil,
        ]);
        
        // Process all bets for this round
        $this->processBets();
    }

    /**
     * Process all bets and update user balances
     */
    public function processBets()
    {
        $bets = $this->bets()->where('status', 'pending')->get();
        
        foreach ($bets as $bet) {
            if ($bet->gem_type === $this->final_result) {
                // User won
                $bet->update([
                    'status' => 'won',
                    'payout_amount' => $bet->amount * $bet->payout_rate,
                ]);
                
                // Add winnings to user balance
                $bet->user->balance += $bet->payout_amount;
                $bet->user->save();
            } else {
                // User lost
                $bet->update([
                    'status' => 'lost',
                ]);
                // Balance was already deducted when bet was placed
            }
            
            // Update commission status from 'pending' to 'available' after bet is processed
            // User có thể rút hoa hồng sau khi bet kết thúc
            \App\Models\UserCommission::where('bet_id', $bet->id)
                ->where('status', 'pending')
                ->update(['status' => 'available']);
        }
    }
}
