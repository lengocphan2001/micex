<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BettingContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'giftcode_usage_id',
        'amount',
        'source',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: GiftcodeUsage
     */
    public function giftcodeUsage()
    {
        return $this->belongsTo(GiftcodeUsage::class);
    }
}
