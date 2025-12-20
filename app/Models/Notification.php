<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'is_read',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
    ];

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        $this->update(['is_read' => false]);
    }

    /**
     * Create a notification for deposit approved
     */
    public static function createDepositApproved($user, $amount, $gemAmount, $depositRequestId)
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'deposit_approved',
            'title' => 'Nạp tiền thành công',
            'message' => "Yêu cầu nạp tiền của bạn đã được duyệt. Số tiền: " . number_format($amount, 0, ',', '.') . " VND (" . number_format($gemAmount, 2, ',', '.') . " đá quý) đã được cộng vào tài khoản.",
            'data' => [
                'amount' => $amount,
                'gem_amount' => $gemAmount,
                'deposit_request_id' => $depositRequestId,
            ],
        ]);
    }

    /**
     * Create a notification for deposit rejected
     */
    public static function createDepositRejected($user, $amount, $depositRequestId, $notes = null)
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'deposit_rejected',
            'title' => 'Nạp tiền bị từ chối',
            'message' => "Yêu cầu nạp tiền của bạn đã bị từ chối. Số tiền: " . number_format($amount, 0, ',', '.') . " VND." . ($notes ? " Lý do: " . $notes : ''),
            'data' => [
                'amount' => $amount,
                'deposit_request_id' => $depositRequestId,
                'notes' => $notes,
            ],
        ]);
    }

    /**
     * Create a notification for withdraw approved
     */
    public static function createWithdrawApproved($user, $amount, $gemAmount, $withdrawRequestId)
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'withdraw_approved',
            'title' => 'Rút tiền thành công',
            'message' => "Yêu cầu rút tiền của bạn đã được duyệt. Số tiền: " . number_format($amount, 0, ',', '.') . " VND (" . number_format($gemAmount, 2, ',', '.') . " đá quý) sẽ được chuyển đến tài khoản ngân hàng của bạn.",
            'data' => [
                'amount' => $amount,
                'gem_amount' => $gemAmount,
                'withdraw_request_id' => $withdrawRequestId,
            ],
        ]);
    }

    /**
     * Create a notification for withdraw rejected
     */
    public static function createWithdrawRejected($user, $amount, $gemAmount, $withdrawRequestId, $notes = null)
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'withdraw_rejected',
            'title' => 'Rút tiền bị từ chối',
            'message' => "Yêu cầu rút tiền của bạn đã bị từ chối. Số tiền: " . number_format($amount, 0, ',', '.') . " VND (" . number_format($gemAmount, 2, ',', '.') . " đá quý) đã được hoàn lại vào tài khoản." . ($notes ? " Lý do: " . $notes : ''),
            'data' => [
                'amount' => $amount,
                'gem_amount' => $gemAmount,
                'withdraw_request_id' => $withdrawRequestId,
                'notes' => $notes,
            ],
        ]);
    }

    /**
     * Create a notification for new promotion
     */
    public static function createPromotionNotification($user, $promotion)
    {
        $percentage = number_format($promotion->deposit_percentage, 2, ',', '.');
        $startDate = $promotion->start_date ? \Carbon\Carbon::parse($promotion->start_date)->format('d/m/Y') : '';
        $endDate = $promotion->end_date ? \Carbon\Carbon::parse($promotion->end_date)->format('d/m/Y') : '';
        
        $message = "Khuyến mãi nạp tiền mới: Nhận thêm {$percentage}% khi nạp tiền.";
        if ($startDate && $endDate) {
            $message .= " Thời gian: {$startDate} - {$endDate}.";
        }
        
        return self::create([
            'user_id' => $user->id,
            'type' => 'promotion',
            'title' => 'Khuyến mãi mới',
            'message' => $message,
            'data' => [
                'promotion_id' => $promotion->id,
                'deposit_percentage' => $promotion->deposit_percentage,
                'start_date' => $promotion->start_date,
                'end_date' => $promotion->end_date,
            ],
        ]);
    }
}
