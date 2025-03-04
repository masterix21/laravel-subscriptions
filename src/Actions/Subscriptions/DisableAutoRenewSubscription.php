<?php

namespace LucaLongo\Subscriptions\Actions\Subscriptions;

use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;

class DisableAutoRenewSubscription
{
    public function execute(SubscriptionContract $subscription): bool
    {
        if (! $subscription->auto_renew) {
            return true;
        }

        $subscription->auto_renew = false;

        return $subscription->save();
    }
}
