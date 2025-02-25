<?php

namespace LucaLongo\Subscriptions\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface PlanContract
{
    public function subscriptions(): HasMany;

    public function upgrades(): BelongsToMany;

    public function downgrades(): BelongsToMany;
}
