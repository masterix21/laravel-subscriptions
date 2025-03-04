<?php

namespace LucaLongo\Subscriptions\Actions\Subscriptions;

use Carbon\Carbon;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Models\Plan;

class RenewSubscription
{
    public function execute(SubscriptionContract $subscription, ?Carbon $nextBillingAt = null): bool
    {
        /** @var Plan $plan */
        $plan = $subscription->plan;

        if (! $nextBillingAt) {
            $nextBillingAt = $subscription->next_billing_at ?: $subscription->ends_at;

            if ($nextBillingAt->isPast()) {
                $nextBillingAt = now();
            }

            $nextBillingAt = $nextBillingAt->toImmutable()->add($plan->duration_period, $plan->duration_interval);
        }

        $subscription->status = SubscriptionStatus::ACTIVE;
        $subscription->ends_at = null;
        $subscription->next_billing_at = $nextBillingAt;
        $subscription->canceled_at = null;

        return $subscription->save();
    }
}
