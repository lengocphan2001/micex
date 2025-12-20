<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'gem_amount',
        'transfer_code',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gem_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user who made the deposit request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved the request
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}
