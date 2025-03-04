<?php

namespace LucaLongo\Subscriptions\Actions\Subscriptions;

use Carbon\Carbon;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;

class RevokeSubscription
{
    public function execute(SubscriptionContract $subscription, ?Carbon $revokedAt = null): bool
    {
        $revokedAt ??= now();

        $subscription->status = SubscriptionStatus::REVOKED;

        $subscription->auto_renew = false;

        $subscription->ends_at = $revokedAt;
        $subscription->revoked_at = $revokedAt;
        $subscription->canceled_at = null;
        $subscription->next_billing_at = null;

        return $subscription->save();
    }
}
