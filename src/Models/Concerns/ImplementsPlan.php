<?php

namespace LucaLongo\Subscriptions\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Enums\Duration;
use LucaLongo\Subscriptions\Enums\PlanRelationType;

/** @mixin PlanContract */
trait ImplementsPlan
{
    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('subscriptions.models.subscription'));
    }

    public function upgrades(): BelongsToMany
    {
        return $this->belongsToMany(
            static::class,
            'plan_relations',
            'plan_id',
            'related_plan_id'
        )->wherePivot('relation_type', PlanRelationType::UPGRADE->value);
    }

    public function downgrades(): BelongsToMany
    {
        return $this->belongsToMany(
            static::class,
            'plan_relations',
            'plan_id',
            'related_plan_id'
        )->wherePivot('relation_type', PlanRelationType::DOWNGRADE->value);
    }

    public function getPrice(Duration $duration, ?string $country = null): int
    {
        $country ??= 'worldwide';

        return $pricing[$duration->value][$country]
            ?? $pricing[$duration->value]['worldwide']
            ?? 0;
    }

    public function getFeature(string $key, mixed $default = null): mixed
    {
        return data_get($this->features, $key, $default);
    }
}
