<?php

namespace LucaLongo\Subscriptions\Actions\Subscriptions;

use Carbon\Carbon;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Models\Plan;

class RenewSubscription
{
    public function execute(SubscriptionContract $subscription, ?Carbon $endsAt = null): bool
    {
        /** @var Plan $plan */
        $plan = $subscription->plan;

        $endsAt ??= $subscription->ends_at->toImmutable()->add($plan->duration_period, $plan->duration_interval);

        $subscription->ends_at = $endsAt;

        return $subscription->save();
    }
}
