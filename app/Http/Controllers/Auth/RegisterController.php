<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm(Request $request)
    {
        // Ensure session is started - this is critical for CSRF token to work
        if (!$request->hasSession()) {
            $request->session()->start();
        }
        
        // Ensure CSRF token exists in session
        if (!$request->session()->has('_token')) {
            $request->session()->regenerateToken();
        }
        
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        // Ensure session is available for CSRF validation
        if (!$request->hasSession()) {
            $request->session()->start();
        }
        
        // Trim and uppercase referral code if provided
        if ($request->has('referral_code') && $request->referral_code) {
            $request->merge(['referral_code' => strtoupper(trim($request->referral_code))]);
        }

        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'string', 'regex:/^\+84[0-9]{9,10}$/', 'unique:users,phone_number'],
            'email' => ['required', 'email', 'unique:users,email'],
            'display_name' => ['required', 'string', 'max:255', 'min:3'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'referral_code' => [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if ($value && !User::where('referral_code', strtoupper(trim($value)))->exists()) {
                        $fail('Mã giới thiệu không hợp lệ.');
                    }
                },
            ],
            'terms' => ['required', 'accepted'],
        ], [
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng +84xxxxxxxxx',
            'phone_number.unique' => 'Số điện thoại này đã được sử dụng.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng.',
            'display_name.required' => 'Vui lòng nhập tên hiển thị.',
            'display_name.min' => 'Tên hiển thị phải có ít nhất 3 ký tự.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'terms.required' => 'Bạn phải đồng ý với điều khoản sử dụng.',
            'terms.accepted' => 'Bạn phải đồng ý với điều khoản sử dụng.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Find referrer if referral code is provided
        $referredBy = null;
        if ($request->referral_code) {
            $referredBy = User::where('referral_code', strtoupper(trim($request->referral_code)))->first();
        }

        // Generate unique referral code
        do {
            $referralCode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (User::where('referral_code', $referralCode)->exists());

        // Generate unique transfer code (nội dung chuyển tiền)
        do {
            $transferCode = '0x' . substr(md5(uniqid(rand(), true)), 0, 12);
        } while (User::where('transfer_code', $transferCode)->exists());

        $user = User::create([
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'display_name' => $request->display_name,
            'password' => Hash::make($request->password),
            'referral_code' => $referralCode,
            'referred_by' => $referredBy?->id,
            'transfer_code' => $transferCode,
        ]);

        // Redirect tới màn hình đăng nhập với thông báo thành công
        return redirect()->route('login')
            ->with('success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập để tiếp tục.');
    }
}
