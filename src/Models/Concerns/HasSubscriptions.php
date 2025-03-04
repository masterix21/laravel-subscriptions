<?php

namespace LucaLongo\Subscriptions\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use LucaLongo\Subscriptions\Actions\Plans\SubscribePlan;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;
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

    public function subscribedTo(Plan|string $plan): bool
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

    public function hasActiveFeature(string $feature): bool
    {
        return once(fn () => $this->activeSubscriptions()->with('features')->get())
            ->pluck('features.*.code')
            ->flatten()
            ->unique()
            ->contains($feature);
    }

    public function hasAnyActiveFeatures(Collection|array $features): bool
    {
        $features = collect($features);

        return once(fn () => $this->activeSubscriptions()->with('features')->get())
            ->pluck('features.*.code')
            ->flatten()
            ->unique()
            ->containsAny($features);
    }

    public function hasAllActiveFeatures(Collection|array $features): bool
    {
        $features = collect($features);

        return once(fn () => $this->activeSubscriptions()->with('features')->get())
            ->pluck('features.*.code')
            ->flatten()
            ->unique()
            ->containsAll($features);
    }

    public function subscribe(
        PlanContract $plan,
        SubscriptionStatus $status = SubscriptionStatus::ACTIVE,
        bool $autoRenew = true,
        array $data = []
    ): SubscriptionContract
    {
        return app(SubscribePlan::class)->subscribe($plan, $this, $status, $autoRenew, $data);
    }
}
