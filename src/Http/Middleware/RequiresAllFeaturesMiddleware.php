<?php

namespace LucaLongo\Subscriptions\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequiresAllFeaturesMiddleware
{
    public function handle(Request $request, Closure $next, string $features)
    {
        $features = str($features)->split("/[\s,|]+/");

        if (! $request->user()?->hasAllActiveFeatures($features)) {
            abort(403);
        }

        return $next($request);
    }
}
