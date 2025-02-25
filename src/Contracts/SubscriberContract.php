<?php

namespace LucaLongo\Subscriptions\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface SubscriberContract
{
    public function subscriptions(): MorphMany;

    public function activeSubscriptions(): MorphMany;
}
