<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdminAuthenticated
{
    /**
     * Handle an incoming request.
     * 
     * Middleware này redirect admin đã đăng nhập khỏi trang login.
     * Session data đã được tách biệt tự động bởi Laravel dựa trên guard name.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}

