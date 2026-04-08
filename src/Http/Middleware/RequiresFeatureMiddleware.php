<?php

namespace LucaLongo\Subscriptions\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LucaLongo\Subscriptions\Models\Concerns\HasSubscriptions;

class RequiresFeatureMiddleware
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        /** @var (Model&HasSubscriptions)|null $user */
        $user = $request->user();

        if (! $user || ! $user->hasActiveFeature($feature)) {
            abort(403);
        }

        return $next($request);
    }
}
