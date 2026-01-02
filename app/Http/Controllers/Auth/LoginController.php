<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(Request $request)
    {
        // If user is already logged in, redirect to dashboard
        if (Auth::guard('web')->check()) {
            return redirect()->intended('/dashboard');
        }
        
        // Ensure session is started - this is critical for CSRF token to work
        if (!$request->hasSession()) {
            $request->session()->start();
        }
        
        // Ensure CSRF token exists in session
        if (!$request->session()->has('_token')) {
            $request->session()->regenerateToken();
        }
        
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        // Ensure session is available for CSRF validation
        if (!$request->hasSession()) {
            $request->session()->start();
        }
        
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập không chính xác.'],
            ]);
        }

        // Login user first
        Auth::guard('web')->login($user, $request->boolean('remember'));
        
        // Regenerate session ID for security (this prevents session fixation attacks)
        $request->session()->regenerate();
        
        // Regenerate CSRF token after session regeneration
        $request->session()->regenerateToken();

        // Enforce single-device login: pin the current session id as the only active session
        if (Schema::hasColumn('users', 'current_session_id')) {
            $user->forceFill([
                'current_session_id' => $request->session()->getId(),
            ])->save();
        }

        return redirect()->intended('/dashboard');
    }

    /**
     * Handle a logout request.
     * This method is designed to work even if CSRF token is expired.
     */
    public function logout(Request $request)
    {
        try {
            // Logout user first
            Auth::guard('web')->logout();

            // Invalidate and regenerate session completely
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $request->session()->flush();
            }
        } catch (\Exception $e) {
            // Even if there's an error, try to clear everything
            try {
                Auth::guard('web')->logout();
            } catch (\Exception $e2) {
                // Ignore
            }
        }

        // Always redirect to login page with success message
        return redirect()->route('login')->with('success', 'Đăng xuất thành công.');
    }
}
