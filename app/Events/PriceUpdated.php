<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PriceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $symbol;
    public float $price;

    public function __construct(string $symbol, float $price)
    {
        $this->symbol = $symbol;
        $this->price = $price;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('price-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'price.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'symbol' => $this->symbol,
            'price' => $this->price,
            'timestamp' => now()->timestamp,
        ];
    }
}
