<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->status !== 1) {
            Auth::logout();
            return redirect('/login')->withErrors([
                'email' => __('auth.inactive'),
            ]);
        }

        return $next($request);
    }
}
