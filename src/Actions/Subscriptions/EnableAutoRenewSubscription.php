<?php

namespace LucaLongo\Subscriptions\Actions\Subscriptions;

use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;

class EnableAutoRenewSubscription
{
    public function execute(SubscriptionContract $subscription): bool
    {
        if ($subscription->auto_renew) {
            return true;
        }

        $subscription->auto_renew = true;

        return $subscription->save();
    }
}
