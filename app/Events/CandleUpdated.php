<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CandleUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $symbol;
    public string $timeframe;
    public array $candle;

    public function __construct(string $symbol, string $timeframe, array $candle)
    {
        $this->symbol = $symbol;
        $this->timeframe = $timeframe;
        $this->candle = $candle;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('candle-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'candle.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'symbol' => $this->symbol,
            'timeframe' => $this->timeframe,
            'candle' => $this->candle,
        ];
    }
}
