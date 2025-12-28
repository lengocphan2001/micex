<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LuckyMoneyOpen extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'opened_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'opened_date' => 'date',
    ];

    /**
     * Relationship: User who opened the lucky money
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if user has opened lucky money today (after 7 AM)
     */
    public static function hasOpenedToday($userId): bool
    {
        $now = now();
        $today = $now->copy();
        
        // If current time is before 7 AM, check yesterday's date
        if ($now->hour < 7) {
            $today = $now->copy()->subDay();
        }
        
        return self::where('user_id', $userId)
            ->whereDate('opened_date', $today->format('Y-m-d'))
            ->exists();
    }

    /**
     * Get today's date for lucky money (resets at 7 AM)
     */
    public static function getTodayDate(): \Carbon\Carbon
    {
        $now = now();
        
        // If current time is before 7 AM, use yesterday's date
        if ($now->hour < 7) {
            return $now->copy()->subDay();
        }
        
        return $now->copy();
    }
}
