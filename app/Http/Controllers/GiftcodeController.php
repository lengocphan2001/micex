<?php

namespace App\Http\Controllers;

use App\Models\Giftcode;
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
            // Use giftcode
            $giftcode->useForUser($user->id, $giftcode->value);

            // Add balance to user
            $user->balance += $giftcode->value;
            $user->save();

            DB::commit();

            return response()->json([
                'message' => "Đã nhận " . number_format($giftcode->value, 2) . " đá quý từ giftcode thành công!",
                'balance' => $user->balance,
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
