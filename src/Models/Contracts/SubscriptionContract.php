<?php

namespace LucaLongo\Subscriptions\Models\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;

/**
 * @property int $id
 * @property SubscriptionStatus $status
 * @property string|null $payment_provider
 * @property string|null $payment_provider_reference
 * @property bool $auto_renew
 * @property int $plan_id
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property \Illuminate\Support\Carbon|null $next_billing_at
 * @property string|null $price
 * @property \Illuminate\Support\Carbon|null $trial_ends_at
 * @property \Illuminate\Support\Carbon|null $grace_ends_at
 * @property \Illuminate\Support\Carbon|null $canceled_at
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property ArrayObject|null $meta
 */
interface SubscriptionContract
{
    public function subscriber(): MorphTo;

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
