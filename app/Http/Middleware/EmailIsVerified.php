<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailIsVerified
{

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user();

        if (!$user || !$user->email_verified_at) {
            return route('return-verification-missing');
        }

        return $next($request);
    }
}
