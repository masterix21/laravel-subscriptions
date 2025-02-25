<?php

namespace LucaLongo\Subscriptions\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface SubscriptionContract
{
    public function subscriber(): MorphTo;

    public function plan(): BelongsTo;

    public function upgradeTo(PlanContract $newPlan): bool;

    public function downgradeTo(PlanContract $newPlan): bool;
}
