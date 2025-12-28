<?php

namespace App\Http\Controllers;

use App\Models\LuckyMoneyOpen;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LuckyMoneyController extends Controller
{
    /**
     * Get lucky money status for current user
     */
    public function getStatus()
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json([
                'message' => 'Bạn chưa đăng nhập.',
            ], 401);
        }

        $hasOpenedToday = LuckyMoneyOpen::hasOpenedToday($user->id);
        $maxGems = (int) SystemSetting::getValue('lucky_money_max_gems', '5');
        
        // Get today's date (resets at 7 AM)
        $today = LuckyMoneyOpen::getTodayDate();
        
        return response()->json([
            'has_opened_today' => $hasOpenedToday,
            'max_gems' => $maxGems,
            'today_date' => $today->format('Y-m-d'),
        ]);
    }

    /**
     * Open lucky money box
     */
    public function open(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json([
                'message' => 'Bạn chưa đăng nhập.',
            ], 401);
        }

        // Check if user has already opened today
        if (LuckyMoneyOpen::hasOpenedToday($user->id)) {
            return response()->json([
                'message' => 'Bạn đã mở hộp quà hôm nay rồi. Vui lòng quay lại vào ngày mai sau 7h sáng.',
            ], 400);
        }

        // Get max gems from system settings
        $maxGems = (float) SystemSetting::getValue('lucky_money_max_gems', '5');
        
        if ($maxGems < 0.1) {
            return response()->json([
                'message' => 'Hệ thống chưa được cấu hình. Vui lòng liên hệ admin.',
            ], 400);
        }

        // Generate random amount from 0.1 to maxGems with 2 decimal places
        // Convert to cents (multiply by 100) for integer random, then divide back
        $minCents = 10; // 0.1 * 100
        $maxCents = (int)($maxGems * 100);
        $amountCents = mt_rand($minCents, $maxCents);
        $amount = round($amountCents / 100, 2);

        DB::beginTransaction();
        try {
            // Get today's date (resets at 7 AM)
            $today = LuckyMoneyOpen::getTodayDate();
            
            // Create lucky money open record
            $luckyMoneyOpen = LuckyMoneyOpen::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'opened_date' => $today->format('Y-m-d'),
            ]);

            // Add balance to user
            $user->balance += $amount;
            $user->save();

            DB::commit();

            // Refresh user to get updated data
            $user->refresh();

            return response()->json([
                'message' => "Chúc mừng! Bạn đã nhận được " . number_format($amount, 2) . " đá quý!",
                'amount' => $amount,
                'balance' => $user->balance,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Lucky money open error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Có lỗi xảy ra khi mở hộp quà. Vui lòng thử lại.',
            ], 500);
        }
    }
}
