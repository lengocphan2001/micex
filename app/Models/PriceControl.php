<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceControl extends Model
{
    protected $table = 'price_control';

    protected $fillable = [
        'symbol',
        'mode',
        'strength',
        'enabled',
        'bias_dir',
        'last_seconds',
        'bias_power',
    ];

    protected $casts = [
        'strength' => 'integer',
        'enabled' => 'boolean',
        'bias_dir' => 'integer',
        'last_seconds' => 'integer',
        'bias_power' => 'integer',
    ];

    /**
     * Get or create price control for a symbol
     */
    public static function getOrCreate(string $symbol): self
    {
        return self::firstOrCreate(
            ['symbol' => $symbol],
            [
                'mode' => 'normal',
                'strength' => 1,
                'enabled' => false,
                'bias_dir' => 0,
                'last_seconds' => 10,
                'bias_power' => 10,
            ]
        );
    }
}
