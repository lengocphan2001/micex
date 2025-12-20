<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_percentage',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'deposit_percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get active promotion for a given date
     */
    public static function getActivePromotion($date = null)
    {
        $date = $date ?? now();
        
        return self::where('is_active', true)
            ->where(function($query) use ($date) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', $date);
            })
            ->where(function($query) use ($date) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $date);
            })
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Check if promotion is currently active
     */
    public function isCurrentlyActive()
    {
        $now = now();
        return $this->is_active 
            && $this->start_date <= $now 
            && $this->end_date >= $now;
    }
}
