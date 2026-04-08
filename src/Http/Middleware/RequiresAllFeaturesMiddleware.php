<?php

namespace LucaLongo\Subscriptions\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LucaLongo\Subscriptions\Models\Concerns\HasSubscriptions;

class RequiresAllFeaturesMiddleware
{
    public function handle(Request $request, Closure $next, string $features)
    {
        $features = str($features)->split("/[\s,|]+/");

        /** @var (Model&HasSubscriptions)|null $user */
        $user = $request->user();

        if (! $user || ! $user->hasAllActiveFeatures($features)) {
            abort(403);
        }

        return $next($request);
    }
}
