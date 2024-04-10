<?php

namespace LucaLongo\Subscriptions\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequiresFeatureMiddleware
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        if (! $request->user()?->hasActiveFeature($feature)) {
            abort(403);
        }

        return $next($request);
    }
}
