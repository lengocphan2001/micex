<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftcodeUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'giftcode_id',
        'user_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship: Giftcode
     */
    public function giftcode()
    {
        return $this->belongsTo(Giftcode::class);
    }

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
