<?php

namespace App\Console\Commands;

use App\Events\PriceUpdated;
use App\Models\PriceControl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PriceTick extends Command
{
    protected $signature = 'price:tick';
    protected $description = 'Generate price tick every second (Price Engine)';

    public function handle()
    {
        $symbols = ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT'];
        
        foreach ($symbols as $symbol) {
            $this->processSymbol($symbol);
        }
    }

    private function processSymbol(string $symbol)
    {
        // Get current price from cache (default: 94000 for BTC, adjust for others)
        $defaultPrice = $this->getDefaultPrice($symbol);
        $price = Cache::get("{$symbol}_PRICE", $defaultPrice);
        
        // Get base price (Binance price hoặc simulated price)
        $basePrice = Cache::get("{$symbol}_BASE_PRICE", $price);
        
        // Update base price (follow Binance hoặc random walk)
        $basePrice += rand(-2, 2) * 0.5; // Simulate Binance movement
        Cache::put("{$symbol}_BASE_PRICE", $basePrice, now()->addMinutes(10));

        // Get price control settings
        $control = PriceControl::getOrCreate($symbol);

        // Calculate price movement (follow speed: 18% như trong file HTML)
        $followSpeed = 0.18;
        $move = ($basePrice - $price) * $followSpeed;

        // Check if we're in the last seconds of a candle (for 1m timeframe)
        $now = now()->timestamp;
        $candleTime = floor($now / 60) * 60; // 1 minute candle
        $remain = 60 - ($now - $candleTime);
        
        // Get current candle from cache
        $candleCacheKey = "{$symbol}_1m_CANDLE";
        $candle = Cache::get($candleCacheKey);
        
        // ÉP NẾN TRONG GIÂY CUỐI (logic từ file HTML)
        if ($control->enabled && $control->bias_dir != 0 && $candle && $remain <= $control->last_seconds) {
            // Calculate target price
            $target = $candle['open'] + ($control->bias_dir * $control->bias_power);
            
            // Calculate distance to target
            $dist = $target - $price;
            
            // Step = 8% of distance (như trong file HTML)
            $step = $dist * 0.08;
            
            // ÉP GHI ĐÈ base price movement
            $move = $step;
        }

        // Apply move
        $price += $move;

        // Ensure price doesn't go negative
        $price = max($price, 1);

        // Store in cache
        Cache::put("{$symbol}_PRICE", $price);

        // Broadcast price update
        event(new PriceUpdated($symbol, $price));
    }

    private function getDefaultPrice(string $symbol): float
    {
        return match($symbol) {
            'BTCUSDT' => 94000,
            'ETHUSDT' => 3500,
            'BNBUSDT' => 600,
            'SOLUSDT' => 100,
            default => 1000,
        };
    }
}
