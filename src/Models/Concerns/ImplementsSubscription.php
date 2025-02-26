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

    public function getFeature(string $key, mixed $defaultValue = null): mixed
    {
        return $this->plan->getFeature($key, $defaultValue);
    }

    public function getConsumedFeature(string $key): int
    {
        return data_get($this->consumed_features, $key, 0);
    }

    public function canConsumeFeature(string $key): bool
    {
        return app(SubscriptionRepositoryInterface::class)->canConsumeFeature($this, $key);
    }

    public function consumeFeature(string $key, int $amount = 1): bool
    {
        return app(SubscriptionRepositoryInterface::class)->consumeFeature($this, $key, $amount);
    }

    public function canStackPlan(): bool
    {
        return app(SubscriptionRepositoryInterface::class)->canStackPlan($this);
    }

    public function reactivate(): bool
    {
        return app(SubscriptionRepositoryInterface::class)->reactivate($this);
    }

    public function getRemainingDays(): int
    {
        return now()->diffInDays($this->ends_at);
    }

    public function getRemainingValue(): int
    {
        return round($this->price / $this->billing_cycle->toDays() * $this->getRemainingDays());
    }
}
