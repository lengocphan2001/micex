<?php

namespace App\Console\Commands;

use App\Events\CandleUpdated;
use App\Models\Candle;
use App\Models\PriceControl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProcessCandles extends Command
{
    protected $signature = 'candle:process {--timeframe=1m}';
    protected $description = 'Process candles from price ticks (Candle Engine)';

    private array $timeframes = ['1m', '5m', '15m', '1h', '4h', '1d'];

    public function handle()
    {
        $timeframe = $this->option('timeframe');
        $symbols = ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT'];

        // Ưu tiên process 1m trước để đảm bảo realtime updates
        foreach ($symbols as $symbol) {
            // Process 1m first (most important for realtime)
            $this->processCandle($symbol, '1m');
            
            // Then process other timeframes if no specific timeframe requested
            if (!$timeframe) {
                foreach ($this->timeframes as $tf) {
                    if ($tf === '1m') continue; // Already processed
                    $this->processCandle($symbol, $tf);
                }
            } elseif ($timeframe !== '1m') {
                $this->processCandle($symbol, $timeframe);
            }
        }
    }

    private function processCandle(string $symbol, string $timeframe)
    {
        // Get current price
        $price = Cache::get("{$symbol}_PRICE", $this->getDefaultPrice($symbol));

        // Get current candle from cache
        $cacheKey = "{$symbol}_{$timeframe}_CANDLE";
        $candle = Cache::get($cacheKey);

        // Calculate candle time based on timeframe
        $candleTime = $this->getCandleTime($timeframe);
        $currentTime = now()->timestamp;

        // Check if we need a new candle
        if (!$candle || $candle['time'] !== $candleTime) {
            // Save previous candle to database if exists
            if ($candle) {
                $this->saveCandle($symbol, $timeframe, $candle);
            }

            // Create new candle
            $candle = [
                'time' => $candleTime,
                'open' => $price,
                'high' => $price,
                'low' => $price,
                'close' => $price,
                'volume' => 0,
            ];
        }

        // Update candle with current price
        $candle['high'] = max($candle['high'], $price);
        $candle['low'] = min($candle['low'], $price);
        $candle['close'] = $price;
        $candle['volume'] += rand(1, 10); // Simulate volume

        // Apply price control (logic ép nến đã được xử lý trong PriceTick)
        // Ở đây chỉ cần đảm bảo candle được update đúng với giá hiện tại
        // Logic ép nến trong giây cuối đã được xử lý trong PriceTick command

        // Save to cache
        Cache::put($cacheKey, $candle, now()->addMinutes(10));

        // Broadcast candle update (mỗi giây cho 1m để nến nhảy realtime)
        event(new CandleUpdated($symbol, $timeframe, $candle));
        
        // Log để debug (chỉ log cho BTCUSDT 1m)
        if ($symbol === 'BTCUSDT' && $timeframe === '1m') {
            $this->info("Broadcasting candle update: {$symbol} {$timeframe} - Close: {$candle['close']}");
        }
        
        // If this is a 1m candle and it just closed (new candle created), process trading bets
        if ($timeframe === '1m') {
            $currentCandleTime = $this->getCandleTime('1m');
            $previousCandleTime = $currentCandleTime - 60;
            
            // Check if we just saved a candle (meaning previous candle closed)
            $previousCandle = Candle::where('symbol', $symbol)
                ->where('timeframe', '1m')
                ->where('time', $previousCandleTime)
                ->first();
            
            if ($previousCandle) {
                // Previous candle exists, check if we need to process bets
            }
        }
    }

    private function applyPriceControl(array &$candle, PriceControl $control)
    {
        switch ($control->mode) {
            case 'down':
                // ÉP đảo chiều (Long chết) - Red candle
                $candle['close'] = $candle['open'] - rand(20, 60) * $control->strength;
                $candle['high'] = max($candle['open'], $candle['close']) + rand(5, 15);
                $candle['low'] = min($candle['open'], $candle['close']) - rand(5, 15);
                break;

            case 'up':
                // ÉP đảo chiều (Short chết) - Green candle
                $candle['close'] = $candle['open'] + rand(20, 60) * $control->strength;
                $candle['high'] = max($candle['open'], $candle['close']) + rand(5, 15);
                $candle['low'] = min($candle['open'], $candle['close']) - rand(5, 15);
                break;

            case 'trap':
                // Random trap - high volatility
                $direction = rand(0, 1) ? 1 : -1;
                $candle['close'] = $candle['open'] + ($direction * rand(30, 80) * $control->strength);
                $candle['high'] = max($candle['open'], $candle['close']) + rand(10, 20);
                $candle['low'] = min($candle['open'], $candle['close']) - rand(10, 20);
                break;
        }
    }

    private function saveCandle(string $symbol, string $timeframe, array $candle)
    {
        Candle::updateOrCreate(
            [
                'symbol' => $symbol,
                'timeframe' => $timeframe,
                'time' => $candle['time'],
            ],
            [
                'open' => $candle['open'],
                'high' => $candle['high'],
                'low' => $candle['low'],
                'close' => $candle['close'],
                'volume' => $candle['volume'],
            ]
        );
    }

    private function getCandleTime(string $timeframe): int
    {
        $now = now();
        $seconds = match($timeframe) {
            '1m' => 60,
            '5m' => 300,
            '15m' => 900,
            '1h' => 3600,
            '4h' => 14400,
            '1d' => 86400,
            default => 60,
        };

        return floor($now->timestamp / $seconds) * $seconds;
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
