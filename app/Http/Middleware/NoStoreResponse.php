<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoStoreResponse
{
    /**
     * Prevent caching of HTML responses that contain CSRF tokens.
     * This reduces "stale CSRF token" issues after deploy, back/forward, and in PWAs.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only apply to typical web responses
        if (method_exists($response, 'headers')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}


