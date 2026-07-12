<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottlePublicApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'public-api:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests.',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
