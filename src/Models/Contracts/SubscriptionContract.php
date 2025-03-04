<?php

namespace LucaLongo\Subscriptions\Models\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

interface SubscriptionContract
{
    public function plan(): BelongsTo;

    public function isRevokable(): Attribute;

    public function isRevoked(): Attribute;

    public function isTrialPeriod(): Attribute;

    public function isGracePeriod(): Attribute;

    public function isActive(): Attribute;

    public function hasFeature(string $feature): bool;

    public function hasAnyFeature(Collection $features): bool;

    public function hasAllFeature(Collection $features): bool;

    public function renew(?Carbon $endsAt = null): bool;

    public function cancel(?Carbon $endsAt = null): bool;

    public function revoke(?Carbon $revokesAt = null): bool;
}
