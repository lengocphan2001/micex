<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'guest.admin' => \App\Http\Middleware\RedirectIfAdminAuthenticated::class,
            'set.admin.guard' => \App\Http\Middleware\SetAdminGuard::class,
            'single.session' => \App\Http\Middleware\EnsureSingleDeviceSession::class,
            'no.store' => \App\Http\Middleware\NoStoreResponse::class,
        ]);
        
        // Exclude logout from CSRF verification (both user and admin logout)
        $middleware->validateCsrfTokens(except: [
            'logout',
            'admin/logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle CSRF token mismatch (419 Page Expired)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            // For AJAX/JSON requests, return JSON response
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'CSRF token mismatch',
                    'message' => 'Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang và thử lại.',
                    'requires_refresh' => true,
                ], 419);
            }

            // For normal web requests: never show the 419 "Page Expired" screen.
            // Regenerate the token and redirect back (or to login) with a friendly message.
            try {
                if ($request->hasSession()) {
                    $request->session()->regenerateToken();
                }
            } catch (\Throwable $t) {
                // ignore
            }
            
            // Handle logout - allow it even with expired token
            if ($request->is('logout') || $request->routeIs('logout')) {
                // Force logout and redirect to login
                try {
                    \Illuminate\Support\Facades\Auth::guard('web')->logout();
                    if ($request->hasSession()) {
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                    }
                } catch (\Exception $e) {
                    // Ignore errors during logout
                }
                return redirect()->route('login')->with('success', 'Đăng xuất thành công.');
            }
            
            // Handle admin login
            if ($request->is('admin/login') || $request->routeIs('admin.login')) {
                return redirect()->route('admin.login')
                    ->with('error', 'Phiên đăng nhập đã hết hạn. Vui lòng thử lại.');
            }
            
            // Handle user login
            if ($request->is('login') || $request->routeIs('login')) {
                return redirect()->route('login')
                    ->with('error', 'Phiên đăng nhập đã hết hạn. Vui lòng thử lại.');
            }
            
            // Handle user register
            if ($request->is('register') || $request->routeIs('register')) {
                return redirect()->route('register')
                    ->with('error', 'Phiên đăng nhập đã hết hạn. Vui lòng thử lại.');
            }
            
            // Handle deposit submit - return JSON for AJAX requests
            if ($request->is('deposit/submit') || $request->routeIs('deposit.submit')) {
                return response()->json([
                    'error' => 'CSRF token mismatch',
                    'message' => 'Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang và thử lại.',
                    'requires_refresh' => true,
                ], 419);
            }
            
            // Prefer referer to avoid redirecting to "/" when back URL is missing.
            $referer = $request->headers->get('referer');
            if ($referer) {
                return redirect()->to($referer)
                    ->withInput($request->except('password', '_token'))
                    ->with('error', 'Phiên đăng nhập đã hết hạn. Vui lòng thử lại.');
            }

            return redirect()->route('login')
                ->with('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        });
    })->create();
