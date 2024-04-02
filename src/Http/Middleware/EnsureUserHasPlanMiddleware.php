<?php

namespace LucaLongo\Subscriptions\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasPlanMiddleware
{
    public function handle(Request $request, Closure $next, string $planCode)
    {
        if (! $request->user()->)

        return $next($request);
    }
}
