<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candle extends Model
{
    protected $fillable = [
        'symbol',
        'timeframe',
        'time',
        'open',
        'high',
        'low',
        'close',
        'volume',
    ];

    protected $casts = [
        'time' => 'integer',
        'open' => 'decimal:8',
        'high' => 'decimal:8',
        'low' => 'decimal:8',
        'close' => 'decimal:8',
        'volume' => 'decimal:8',
    ];

    public $timestamps = true;
}
