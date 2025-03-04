<?php

namespace LucaLongo\Subscriptions\Actions\Subscriptions;

use Carbon\Carbon;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;

class CancelSubscription
{
    public function execute(SubscriptionContract $subscription, ?Carbon $endsAt = null): bool
    {
        $endsAt ??= $subscription->next_billing_at
            ?: $subscription->ends_at
            ?: $subscription->canceled_at
            ?: now();

        $subscription->ends_at = $endsAt;
        $subscription->next_billing_at = null;
        $subscription->auto_renew = false;
        $subscription->status = SubscriptionStatus::CANCELED;
        $subscription->canceled_at = now();

        return $subscription->save();
    }
}
