<?php

namespace App\Http\Controllers;

use App\Models\DepositRequest;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    /**
     * Submit a deposit request
     */
    public function submit(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Bạn chưa đăng nhập. Vui lòng đăng nhập lại.');
        }

        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:100000|max:500000000',
                'transfer_code' => 'nullable|string|max:255',
            ]);

            // Get VND to Gem rate from settings
            $vndToGemRate = SystemSetting::getVndToGemRate();
            
            // Calculate gem amount based on rate
            $gemAmount = $validated['amount'] / $vndToGemRate;

            // Create deposit request
            $depositRequest = DepositRequest::create([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'gem_amount' => $gemAmount,
                'transfer_code' => $validated['transfer_code'] ?? $user->transfer_code,
                'status' => 'pending',
            ]);

            // Return JSON for AJAX requests, redirect for normal requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Yêu cầu nạp tiền đã được gửi thành công. .',
                    'deposit_request_id' => $depositRequest->id,
                ]);
            }

            return redirect()->route('deposit.bank')->with('success', 'Yêu cầu nạp tiền đã được gửi thành công. .');
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
     * Simple polling endpoint to check deposit request status
     * Changed from long polling to simple polling to avoid keeping connections open
     */
    public function checkStatus(Request $request, $id)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        // Ensure the deposit request belongs to the authenticated user
        $depositRequest = DepositRequest::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$depositRequest) {
            return response()->json([
                'error' => 'Deposit request not found',
            ], 404);
        }

        // Simple polling: just return current status immediately
        // Client will poll again after a delay if still pending
        return response()->json([
            'status' => $depositRequest->status,
            'message' => $this->getStatusMessage($depositRequest->status),
            'deposit_request' => [
                'id' => $depositRequest->id,
                'status' => $depositRequest->status,
                'amount' => $depositRequest->amount,
                'gem_amount' => $depositRequest->gem_amount,
            ],
        ]);
    }

    /**
     * Get status message
     */
    private function getStatusMessage(string $status): string
    {
        return match ($status) {
            'pending' => 'Yêu cầu đang được xử lý. .',
            'approved' => 'Yêu cầu nạp tiền đã được duyệt thành công!',
            'rejected' => 'Yêu cầu nạp tiền đã bị từ chối.',
            default => 'Trạng thái không xác định.',
        };
    }
}
