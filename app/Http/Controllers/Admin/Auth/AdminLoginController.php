<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminLoginController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        
        // Ensure session is started
        $request->session()->regenerateToken();
        
        return view('admin.auth.login');
    }

    /**
     * Handle an admin login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        // Tìm admin với email
        $admin = \App\Models\Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập không chính xác.'],
            ]);
        }

        // Login admin
        Auth::guard('admin')->login($admin, $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Handle an admin logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
