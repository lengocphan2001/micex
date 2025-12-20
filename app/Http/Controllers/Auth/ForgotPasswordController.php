<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /**
     * Show the password reset request form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Handle a password reset link request.
     * Note: This is a placeholder. Full implementation would require SMS/OTP service.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'phone_number' => ['required', 'string', 'regex:/^\+84[0-9]{9,10}$/'],
        ], [
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ.',
        ]);

        // TODO: Implement SMS/OTP sending logic here
        // For now, just return with a message
        return back()->with('status', 'Tính năng đặt lại mật khẩu đang được phát triển. Vui lòng liên hệ hỗ trợ.');
    }
}
