<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $plan_id
 * @property int $feature_id
 * @property int|null $max_usage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PlanFeature extends Pivot
{
    public $incrementing = true;

    public $guarded = [];

    protected $casts = [
        'max_usage' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('subscriptions.models.plan'));
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(config('subscriptions.models.feature'));
    }
}
