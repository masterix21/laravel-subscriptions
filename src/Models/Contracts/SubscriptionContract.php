<?php

namespace LucaLongo\Subscriptions\Models\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

interface SubscriptionContract
{
    public function plan(): BelongsTo;

    public function isRevokable(): bool;

    public function isRevoked(): bool;

    public function onTrial(): bool;

    public function onGrace(): bool;

    public function isActive(): bool;

    public function hasFeature(string $feature): bool;

    public function hasAnyFeature(Collection $features): bool;

    public function hasAllFeature(Collection $features): bool;

    public function renew(?Carbon $endsAt = null): bool;

    public function cancel(?Carbon $endsAt = null): bool;

    public function revoke(?Carbon $revokesAt = null): bool;
}
