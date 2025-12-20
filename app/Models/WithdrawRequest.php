<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gem_amount',
        'amount',
        'bank_name',
        'bank_account',
        'bank_full_name',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'gem_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user who made the withdraw request
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
