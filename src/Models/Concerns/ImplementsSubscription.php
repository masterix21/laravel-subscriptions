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
        $limit = $this->plan->getFeature($key);
        $consumed = $this->getConsumedFeature($key);

        return $limit === null || $consumed < $limit;
    }

    public function consumeFeature(string $key, int $amount = 1): bool
    {
        if (!$this->canConsumeFeature($key)) {
            return false;
        }

        $consumed = $this->consumed_features ?: [];

        data_set($consumed, $key, $this->getConsumedFeature($key) + $amount);

        return $this->update([
            'consumed_features' => $consumed
        ]);
    }
}
