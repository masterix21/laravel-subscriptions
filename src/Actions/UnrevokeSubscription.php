<?php

namespace LucaLongo\Subscriptions\Actions;

use LucaLongo\Subscriptions\Events\SubscriptionUnrevoked;
use LucaLongo\Subscriptions\Models\Subscription;

class UnrevokeSubscription
{
    public function execute(Subscription $subscription): bool
    {
        if (! $subscription->is_revoked) {
            return false;
        }

        $subscription->revoked_at = null;

        if (! $subscription->save()) {
            return false;
        }

        event(new SubscriptionUnrevoked($subscription));

        return true;
    }
}
