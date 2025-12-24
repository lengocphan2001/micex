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
        // Use app timezone from config to get correct date
        $appTimezone = config('app.timezone', 'Asia/Ho_Chi_Minh');
        if ($date === null) {
            $date = \Carbon\Carbon::now($appTimezone);
        }
        // Convert to date only (start of day) for proper comparison
        $today = \Carbon\Carbon::parse($date)->timezone($appTimezone)->startOfDay();
        $dateString = $today->format('Y-m-d');
        
        // Get all active promotions and filter in PHP to ensure correct date comparison
        $allActive = self::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $promotion = $allActive->first(function($p) use ($today, $dateString) {
            // Check if promotion is active for today
            // Use date string comparison to avoid timezone issues
            $startDateStr = $p->start_date ? $p->start_date->format('Y-m-d') : null;
            $endDateStr = $p->end_date ? $p->end_date->format('Y-m-d') : null;
            
            // If no start_date, consider it as started
            $started = !$startDateStr || $startDateStr <= $dateString;
            // If no end_date, consider it as never ending
            $notEnded = !$endDateStr || $endDateStr >= $dateString;
            
            $isActive = $started && $notEnded;
            
            // Debug for each promotion
            \Log::info('Checking promotion', [
                'promotion_id' => $p->id,
                'today' => $dateString,
                'start_date' => $startDateStr,
                'end_date' => $endDateStr,
                'started' => $started,
                'notEnded' => $notEnded,
                'isActive' => $isActive,
            ]);
            
            return $isActive;
        });
        
        // Debug logging
        \Log::info('getActivePromotion', [
            'date_string' => $dateString,
            'today_app_timezone' => \Carbon\Carbon::now(config('app.timezone', 'Asia/Ho_Chi_Minh'))->format('Y-m-d'),
            'today_utc' => now()->format('Y-m-d'),
            'found_promotion' => $promotion ? [
                'id' => $promotion->id,
                'start_date' => $promotion->start_date ? $promotion->start_date->format('Y-m-d') : null,
                'end_date' => $promotion->end_date ? $promotion->end_date->format('Y-m-d') : null,
            ] : null,
            'all_active_promotions' => $allActive->map(function($p) {
                return [
                    'id' => $p->id,
                    'start_date' => $p->start_date ? $p->start_date->format('Y-m-d') : null,
                    'end_date' => $p->end_date ? $p->end_date->format('Y-m-d') : null,
                ];
            }),
        ]);
        
        return $promotion;
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
