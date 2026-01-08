<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardTransfer extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
