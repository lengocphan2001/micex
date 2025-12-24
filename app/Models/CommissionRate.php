<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRate extends Model
{
    protected $fillable = [
        'level',
        'rate',
        'is_active',
        'order',
    ];

    protected $casts = [
        'rate' => 'float',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get active commission rates ordered by level
     */
    public static function getActiveRates()
    {
        return self::where('is_active', true)
            ->orderBy('order')
            ->orderBy('level')
            ->get();
    }

    /**
     * Get rate for a specific level
     */
    public static function getRateForLevel($level)
    {
        $rate = self::where('level', $level)
            ->where('is_active', true)
            ->first();
        
        return $rate ? $rate->rate : 0;
    }
}
