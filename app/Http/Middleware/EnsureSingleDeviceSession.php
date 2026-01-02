<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class EnsureSingleDeviceSession
{
    /**
     * Enforce "single device" login for the web guard.
     * When the user logs in on a new device, the latest session id is stored on the user record.
     * Any other device with a different session id will be logged out on the next request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('web')->check()) {
            return $next($request);
        }

        $user = Auth::guard('web')->user();
        $sessionId = $request->session()->getId();

        // If feature isn't migrated yet, do nothing (prevents deploy-time errors).
        if (!Schema::hasColumn('users', 'current_session_id')) {
            return $next($request);
        }

        // If we can't determine a session id, fail open (avoid accidental lockouts).
        if (!$sessionId) {
            return $next($request);
        }

        // Backward compatibility: if this user has no session pinned yet (e.g. after deploy),
        // pin the current session to avoid instantly logging everyone out.
        if (empty($user->current_session_id)) {
            $user->forceFill(['current_session_id' => $sessionId])->save();
            return $next($request);
        }

        // If session doesn't match, this device is no longer the active login.
        if (!hash_equals((string) $user->current_session_id, (string) $sessionId)) {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'SESSION_REVOKED',
                    'message' => 'Tài khoản đã đăng nhập ở thiết bị khác. Vui lòng đăng nhập lại.',
                ], 401);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Tài khoản đã đăng nhập ở thiết bị khác. Vui lòng đăng nhập lại.');
        }

        return $next($request);
    }
}


