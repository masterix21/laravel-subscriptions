<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $feature) {
            $feature->code ??= str($feature->name)->slug();
        });
    }

    protected function casts(): array
    {
        return [
            'meta' => AsArrayObject::class,
        ];
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(config('subscriptions.models.feature'), 'plan_feature')
            ->using(config('subscriptions.models.plan_feature'))
            ->withTimestamps();
    }
}
