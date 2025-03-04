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

        $subscription->ends_at = $revokedAt;
        $subscription->auto_renew = false;
        $subscription->status = SubscriptionStatus::REVOKED;
        $subscription->revoked_at = $revokedAt;

        return $subscription->save();
    }
}
