<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetAdminGuard
{
    /**
     * Handle an incoming request.
     * 
     * Middleware này đảm bảo admin guard được sử dụng riêng biệt với web guard.
     * Laravel tự động tách biệt session data dựa trên guard name:
     * - Web guard: session key 'login_web_...'
     * - Admin guard: session key 'login_admin_...'
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set guard mặc định là 'admin' cho admin routes
        // Điều này đảm bảo Auth::user() và các helper khác sử dụng admin guard
        Config::set('auth.defaults.guard', 'admin');
        
        return $next($request);
    }
}

