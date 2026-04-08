<?php

namespace LucaLongo\Subscriptions\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequiresFeatureMiddleware
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        /** @var (\Illuminate\Database\Eloquent\Model&\LucaLongo\Subscriptions\Models\Concerns\HasSubscriptions)|null $user */
        $user = $request->user();

        if (! $user || ! $user->hasActiveFeature($feature)) {
            abort(403);
        }

        return $next($request);
    }
}
