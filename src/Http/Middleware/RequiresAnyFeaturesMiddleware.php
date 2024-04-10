<?php

namespace LucaLongo\Subscriptions\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequiresAnyFeaturesMiddleware
{
    public function handle(Request $request, Closure $next, string $features)
    {
        $features = str($features)->split("/[\s,|]+/");

        if (! $request->user()?->hasAnyActiveFeatures($features)) {
            abort(403);
        }

        return $next($request);
    }
}
