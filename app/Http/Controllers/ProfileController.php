<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\FundPasswordVerificationCodeMail;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function bankLink(Request $request)
    {
        // Sử dụng web guard để đảm bảo tách biệt với admin guard
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has fund password
        if (empty($user->fund_password)) {
            return back()->withErrors(['fund_password' => 'Bạn chưa tạo mật khẩu quỹ. Vui lòng tạo mật khẩu quỹ trước khi liên kết ngân hàng.'])->withInput();
        }

        $validated = $request->validate([
            'bank_name'      => 'required|string|max:255',
            'bank_account'   => 'required|string|max:255',
            'bank_full_name' => 'required|string|max:255',
            'fund_password'  => 'required|string|min:6',
        ]);

        // Verify fund password
        if (!Hash::check($validated['fund_password'], $user->fund_password)) {
            return back()->withErrors(['fund_password' => 'Mật khẩu quỹ không đúng.'])->withInput();
        }

        // Allow updating / adding another bank: overwrite with latest
        $user->bank_name = $validated['bank_name'];
        $user->bank_account = $validated['bank_account'];
        $user->bank_full_name = $validated['bank_full_name'];
        $user->save();

        return back()->with('status', 'Liên kết / cập nhật ngân hàng thành công.');
    }

    public function changeLoginPassword(Request $request)
    {
        // Sử dụng web guard để đảm bảo tách biệt với admin guard
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'current_password'      => 'required|string',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('status', 'Đổi mật khẩu đăng nhập thành công.');
    }

    /**
     * Create fund password (first time setup)
     */
    public function createFundPassword(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 401);
        }

        // Check if user already has fund password
        if ($user->fund_password) {
            return response()->json([
                'success' => false,
                'error' => 'Bạn đã có mật khẩu quỹ. Vui lòng sử dụng chức năng đổi mật khẩu quỹ.',
            ], 400);
        }

        $validated = $request->validate([
            'fund_password' => 'required|string|min:6|confirmed',
        ]);

        $user->fund_password = Hash::make($validated['fund_password']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Tạo mật khẩu quỹ thành công.',
        ]);
    }

    /**
     * Send verification code for fund password change
     */
    public function sendFundPasswordVerificationCode(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 401);
        }

        // Check if user has fund password
        if (!$user->fund_password) {
            return response()->json([
                'success' => false,
                'error' => 'Bạn chưa có mật khẩu quỹ. Vui lòng tạo mật khẩu quỹ trước.',
            ], 400);
        }

        // Generate 6-digit verification code
        $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store code in session with expiration (1 minute)
        $request->session()->put('fund_password_verification_code', $verificationCode);
        $request->session()->put('fund_password_verification_expires', now()->addMinute(1)->timestamp);

        // Send email
        try {
            Mail::to($user->email)->send(
                new FundPasswordVerificationCodeMail($verificationCode, $user->display_name ?? $user->name)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send verification code email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Không thể gửi email. Vui lòng thử lại sau.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mã xác nhận đã được gửi đến email của bạn.',
        ]);
    }

    /**
     * Change fund password (when user already has one)
     */
    public function changeFundPassword(Request $request)
    {
        // Sử dụng web guard để đảm bảo tách biệt với admin guard
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has fund password
        if (!$user->fund_password) {
            return back()->withErrors(['fund_password' => 'Bạn chưa có mật khẩu quỹ. Vui lòng tạo mật khẩu quỹ trước.']);
        }

        $validated = $request->validate([
            'current_fund_password' => 'required|string',
            'fund_password'         => 'required|string|min:6|confirmed',
            'verification_code'     => 'required|string|max:255',
        ]);

        // Verify current fund password
        if (!Hash::check($validated['current_fund_password'], $user->fund_password)) {
            return back()->withErrors(['current_fund_password' => 'Mật khẩu quỹ hiện tại không đúng.']);
        }

        // Verify verification code
        $storedCode = $request->session()->get('fund_password_verification_code');
        $expiresAt = $request->session()->get('fund_password_verification_expires');
        
        if (!$storedCode || !$expiresAt) {
            return back()->withErrors(['verification_code' => 'Mã xác nhận không hợp lệ hoặc đã hết hạn. Vui lòng gửi lại mã.']);
        }

        if (now()->timestamp > $expiresAt) {
            $request->session()->forget(['fund_password_verification_code', 'fund_password_verification_expires']);
            return back()->withErrors(['verification_code' => 'Mã xác nhận đã hết hạn. Vui lòng gửi lại mã.']);
        }

        if ($storedCode !== $validated['verification_code']) {
            return back()->withErrors(['verification_code' => 'Mã xác nhận không đúng.']);
        }

        // Clear verification code after successful use
        $request->session()->forget(['fund_password_verification_code', 'fund_password_verification_expires']);

        // Update fund password
        $user->fund_password = Hash::make($validated['fund_password']);
        $user->save();

        return back()->with('status', 'Đổi mật khẩu quỹ thành công.');
    }
}

