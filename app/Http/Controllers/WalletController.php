<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Transfer amount from reward wallet to deposit wallet
     */
    public function transferRewardToDeposit(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa đăng nhập.',
            ], 401);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:5', // Tối thiểu 5$
        ], [
            'amount.required' => 'Vui lòng nhập số tiền muốn chuyển.',
            'amount.numeric' => 'Số tiền phải là số.',
            'amount.min' => 'Số tiền chuyển tối thiểu là 5 đá quý.',
        ]);

        if (($user->reward_balance ?? 0) < $validated['amount']) {
            return response()->json([
                'success' => false,
                'message' => 'Số dư ví thưởng không đủ.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Lock user row to prevent race condition
            $user = \App\Models\User::where('id', $user->id)->lockForUpdate()->first();
            
            // Check balance again after lock
            if (($user->reward_balance ?? 0) < $validated['amount']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Số dư ví thưởng không đủ.',
                ], 400);
            }

            // Transfer from reward wallet to deposit wallet
            $user->transferRewardToDeposit($validated['amount']);

            // Log transfer
            \App\Models\RewardTransfer::create([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
            ]);

            DB::commit();

            // Refresh user to get updated data
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Chuyển tiền từ ví thưởng sang ví nạp thành công.',
                'reward_balance' => $user->reward_balance,
                'balance' => $user->balance,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Transfer reward to deposit error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get wallet balances
     */
    public function getBalances()
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa đăng nhập.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'balance' => $user->balance ?? 0,
            'reward_balance' => $user->reward_balance ?? 0,
            'total_balance' => $user->getTotalBalance(),
        ]);
    }
}
