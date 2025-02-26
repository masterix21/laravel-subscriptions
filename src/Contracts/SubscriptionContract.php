<?php

namespace LucaLongo\Subscriptions\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface SubscriptionContract
{
    public function subscriber(): MorphTo;

    public function plan(): BelongsTo;

    public function isOnTrial(): bool;

    public function isOnGracePeriod(): bool;

    public function hasExpired(): bool;

    public function upgradeTo(PlanContract $newPlan): bool;

    public function downgradeTo(PlanContract $newPlan): bool;

    public function isRenewable(): bool;

    public function renew(): bool;

    public function enableAutoRenew(): bool;

    public function disableAutoRenew(): bool;

    public function getFeature(string $key): mixed;

    public function getConsumedFeature(string $key): int;

    public function canConsumeFeature(string $featureKey): bool;

    public function consumeFeature(string $featureKey, int $amount = 1): bool;
}
