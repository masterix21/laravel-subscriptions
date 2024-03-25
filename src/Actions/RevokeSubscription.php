<?php

namespace LucaLongo\Subscriptions\Actions;

use LucaLongo\Subscriptions\Events\SubscriptionRevoked;
use LucaLongo\Subscriptions\Models\Subscription;

class RevokeSubscription
{
    public function execute(Subscription $subscription): bool
    {
        if (! $subscription->is_revokable) {
            return false;
        }

        $subscription->revoked_at = now();

        if (! $subscription->save()) {
            return false;
        }

        event(new SubscriptionRevoked($subscription));

        return true;
    }
}
