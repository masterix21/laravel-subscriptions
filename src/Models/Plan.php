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
            'duration' => 'int',
            'duration_interval' => DurationInterval::class,
            'price' => 'decimal',
            'grace_days' => 'int',
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
