<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LucaLongo\Subscriptions\Enums\DurationInterval;

class Plan extends Model
{
    protected function casts(): array
    {
        return [
            'enabled' => 'bool',
            'hidden' => 'bool',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'duration_period' => 'int',
            'duration_interval' => DurationInterval::class,
            'price' => 'decimal',
            'trial_period' => 'int',
            'trial_interval' => DurationInterval::class,
            'grace_period' => 'int',
            'grace_interval' => DurationInterval::class,
            'meta' => AsArrayObject::class,
        ];
    }

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(config('subscriptions.models.feature'), 'plan_feature')
            ->using(config('subscriptions.models.plan_feature'))
            ->withTimestamps();
    }
}
