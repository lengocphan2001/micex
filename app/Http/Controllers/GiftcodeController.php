<?php

namespace App\Http\Controllers;

use App\Models\Giftcode;
use App\Models\BettingContribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GiftcodeController extends Controller
{
    /**
     * Redeem giftcode
     */
    public function redeem(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json([
                'message' => 'Bạn chưa đăng nhập.',
            ], 401);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim($validated['code']));
        
        $giftcode = Giftcode::where('code', $code)->first();

        if (!$giftcode) {
            \Log::info("Giftcode not found: {$code}");
            return response()->json([
                'message' => 'Mã giftcode không tồn tại.',
            ], 404);
        }

        // Debug logging
        \Log::info("Checking giftcode: {$code}", [
            'is_active' => $giftcode->is_active,
            'expires_at' => $giftcode->expires_at,
            'used_count' => $giftcode->used_count,
            'quantity' => $giftcode->quantity,
        ]);

        if (!$giftcode->canBeUsed()) {
            return response()->json([
                'message' => 'Mã giftcode không thể sử dụng (đã hết hạn hoặc đã hết lượt sử dụng).',
            ], 400);
        }

        if ($giftcode->hasBeenUsedBy($user->id)) {
            return response()->json([
                'message' => 'Bạn đã sử dụng mã giftcode này rồi.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Use giftcode (this creates a GiftcodeUsage record)
            if (!$giftcode->useForUser($user->id, $giftcode->value)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Không thể sử dụng giftcode này.',
                ], 400);
            }

            // Get the giftcode usage record that was just created
            $giftcodeUsage = \App\Models\GiftcodeUsage::where('giftcode_id', $giftcode->id)
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Add balance to user
            $user->balance += $giftcode->value;
            
            // Increase betting requirement by giftcode value
            $user->betting_requirement = ($user->betting_requirement ?? 0) + $giftcode->value;
            
            $user->save();

            // Create betting contribution to count towards betting requirement
            // This allows giftcode value to count towards the betting requirement for withdrawal
            if ($giftcodeUsage) {
                BettingContribution::create([
                    'user_id' => $user->id,
                    'giftcode_usage_id' => $giftcodeUsage->id,
                    'amount' => $giftcode->value,
                    'source' => 'giftcode',
                ]);
            }

            DB::commit();

            // Refresh user to get updated data
            $user->refresh();

            return response()->json([
                'message' => "Đã nhận " . number_format($giftcode->value, 2) . " đá quý từ giftcode thành công!",
                'balance' => $user->balance,
                'betting_requirement' => $user->betting_requirement ?? 0,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Giftcode redemption error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Có lỗi xảy ra khi sử dụng Giftcode. Vui lòng thử lại.',
            ], 500);
        }
    }
}
