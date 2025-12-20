<?php

namespace App\Http\Controllers;

use App\Models\WithdrawRequest;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WithdrawController extends Controller
{
    /**
     * Submit a withdraw request
     */
    public function submit(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Bạn chưa đăng nhập. Vui lòng đăng nhập lại.');
        }

        try {
            $validated = $request->validate([
                'gem_amount' => 'required|numeric|min:0.01',
                'fund_password' => 'required|string',
            ], [
                'gem_amount.required' => 'Vui lòng nhập số lượng đá quý muốn rút.',
                'gem_amount.numeric' => 'Số lượng đá quý phải là số.',
                'gem_amount.min' => 'Số lượng đá quý phải lớn hơn 0.',
                'fund_password.required' => 'Vui lòng nhập mật khẩu quỹ.',
            ]);

            // Check if user has fund password
            if (empty($user->fund_password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa có mật khẩu quỹ. Vui lòng tạo mật khẩu quỹ trước.',
                ], 400);
            }

            // Verify fund password
            if (!Hash::check($validated['fund_password'], $user->fund_password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu quỹ không đúng.',
                ], 400);
            }

            // Check if user has enough balance
            if ($user->balance < $validated['gem_amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số dư không đủ để rút tiền.',
                ], 400);
            }

            // Check if user has bank account linked
            if (empty($user->bank_name) || empty($user->bank_account) || empty($user->bank_full_name)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa liên kết ngân hàng. Vui lòng liên kết ngân hàng trước.',
                ], 400);
            }

            // Get VND to Gem rate from settings
            $vndToGemRate = SystemSetting::getVndToGemRate();
            
            // Calculate VND amount based on rate
            $vndAmount = $validated['gem_amount'] * $vndToGemRate;

            // Create withdraw request
            $withdrawRequest = WithdrawRequest::create([
                'user_id' => $user->id,
                'gem_amount' => $validated['gem_amount'],
                'amount' => $vndAmount,
                'bank_name' => $user->bank_name,
                'bank_account' => $user->bank_account,
                'bank_full_name' => $user->bank_full_name,
                'status' => 'pending',
            ]);

            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Yêu cầu rút tiền đã được gửi thành công. Vui lòng chờ admin duyệt.',
                    'withdraw_request_id' => $withdrawRequest->id,
                ]);
            }

            return redirect()->route('withdraw')->with('success', 'Yêu cầu rút tiền đã được gửi thành công. Vui lòng chờ admin duyệt.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Có lỗi xảy ra: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Check withdraw request status (for polling)
     */
    public function checkStatus(Request $request, $id)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        // Ensure the withdraw request belongs to the authenticated user
        $withdrawRequest = WithdrawRequest::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$withdrawRequest) {
            return response()->json([
                'error' => 'Withdraw request not found',
            ], 404);
        }

        return response()->json([
            'status' => $withdrawRequest->status,
            'message' => $this->getStatusMessage($withdrawRequest->status),
            'withdraw_request' => [
                'id' => $withdrawRequest->id,
                'status' => $withdrawRequest->status,
                'gem_amount' => $withdrawRequest->gem_amount,
                'amount' => $withdrawRequest->amount,
            ],
        ]);
    }

    /**
     * Get status message
     */
    private function getStatusMessage(string $status): string
    {
        return match ($status) {
            'pending' => 'Yêu cầu đang được xử lý. Vui lòng chờ admin duyệt.',
            'approved' => 'Yêu cầu rút tiền đã được duyệt thành công!',
            'rejected' => 'Yêu cầu rút tiền đã bị từ chối.',
            default => 'Trạng thái không xác định.',
        };
    }
}
