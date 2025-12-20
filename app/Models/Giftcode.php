<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Giftcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'quantity',
        'used_count',
        'value',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Giftcode usages
     */
    public function usages()
    {
        return $this->hasMany(\App\Models\GiftcodeUsage::class);
    }

    /**
     * Check if giftcode can be used
     */
    public function canBeUsed()
    {
        // Check if active
        if (!$this->is_active) {
            \Log::info("Giftcode {$this->code} is not active");
            return false;
        }

        // Check expiration date (compare dates only, not time)
        // Giftcode expires at the END of expires_at date, so it should be valid if expires_at >= today
        if ($this->expires_at) {
            $expiresDate = \Carbon\Carbon::parse($this->expires_at)->startOfDay();
            $today = \Carbon\Carbon::today()->startOfDay();
            if ($expiresDate->lt($today)) {
                \Log::info("Giftcode {$this->code} expired. Expires: {$expiresDate->format('Y-m-d')}, Today: {$today->format('Y-m-d')}");
                return false;
            }
        }

        // Check if quantity is exhausted
        if ($this->used_count >= $this->quantity) {
            \Log::info("Giftcode {$this->code} quantity exhausted. Used: {$this->used_count}, Quantity: {$this->quantity}");
            return false;
        }

        return true;
    }

    /**
     * Check if user has already used this giftcode
     */
    public function hasBeenUsedBy($userId)
    {
        return $this->usages()->where('user_id', $userId)->exists();
    }

    /**
     * Use giftcode for a user
     */
    public function useForUser($userId, $amount)
    {
        if (!$this->canBeUsed()) {
            return false;
        }

        if ($this->hasBeenUsedBy($userId)) {
            return false;
        }

        // Create usage record
        $this->usages()->create([
            'user_id' => $userId,
            'amount' => $amount,
        ]);

        // Increment used count
        $this->increment('used_count');

        return true;
    }
}
