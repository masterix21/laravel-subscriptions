<?php

namespace LucaLongo\Subscriptions\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LucaLongo\Subscriptions\Models\Plan;

/** @mixin Model */
trait HasSubscriptions
{
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(config('subscriptions.models.subscription'), 'subscriber');
    }

    public function activeSubscriptions(): MorphMany
    {
        return $this->subscriptions()->active();
    }

    public function hasPlan(Plan|string $plan): bool
    {
        return once(function () use ($plan) {
            return $this
                ->activeSubscriptions()
                ->where(function (Builder $query) use ($plan) {
                    if (is_string($plan)) {
                        return $query->whereRelation('plan', 'code', $plan);
                    }

                    return $query->where('plan_id', $plan->getKey());
                })
                ->exists();
        });
    }
}
