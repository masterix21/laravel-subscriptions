<?php

namespace LucaLongo\Subscriptions\Models\Concerns;

use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Repositories\Contracts\SubscriptionRepositoryInterface;

/** @mixin SubscriptionContract */
trait ImplementsSubscription
{
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && now()->lt($this->trial_ends_at);
    }

    public function isOnGracePeriod(): bool
    {
        return $this->grace_ends_at && now()->lt($this->grace_ends_at);
    }

    public function hasExpired(): bool
    {
        return ! $this->isOnTrial() && ! $this->isOnGracePeriod() && now()->gt($this->ends_at);
    }

    public function upgradeTo(PlanContract $newPlan): bool
    {
        return app(SubscriptionRepositoryInterface::class)->upgrade($this, $newPlan);
    }

    public function downgradeTo(PlanContract $newPlan): bool
    {
        return app(SubscriptionRepositoryInterface::class)->downgrade($this, $newPlan);
    }

    public function isRenewable(): bool
    {
        return app(SubscriptionRepositoryInterface::class)->isRenewable($this);
    }

    public function renew(): bool
    {
        return app(SubscriptionRepositoryInterface::class)->renew($this);
    }

    public function enableAutoRenew(): bool
    {
        return app(SubscriptionRepositoryInterface::class)->enableAutoRenew($this);
    }

    public function disableAutoRenew(): bool
    {
        return app(SubscriptionRepositoryInterface::class)->disableAutoRenew($this);
    }
}
