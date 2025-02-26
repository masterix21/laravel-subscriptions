<?php

namespace LucaLongo\Subscriptions\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LucaLongo\Subscriptions\Enums\Duration;

interface PlanContract
{
    public function subscriptions(): HasMany;

    public function upgrades(): BelongsToMany;

    public function downgrades(): BelongsToMany;

    public function getPrice(Duration $duration, ?string $country = null): int;

    public function getFeature(string $key, mixed $default = null): mixed;
}
