<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Logout route - allow even with expired token
        'logout',
        // Temporarily exclude register and login for testing
        'register',
        'login',
        // Exclude deposit submit from CSRF
        'deposit/submit',
        // Exclude fund password routes from CSRF
        'me/send-fund-password-verification-code',
    ];
}
