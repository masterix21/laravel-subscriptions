<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PlanFeature extends Pivot
{
    public $incrementing = true;

    public $guarded = [];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('subscriptions.models.plan'));
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(config('subscriptions.models.feature'));
    }
}
