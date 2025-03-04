<?php

namespace LucaLongo\Subscriptions\Models\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;

interface SubscriberContract
{
    public function displayLabel(): Attribute;

    public function subscribe(
        PlanContract $plan,
        SubscriptionStatus $status = SubscriptionStatus::ACTIVE,
        bool $autoRenew = true,
        array $data = []
    ): SubscriptionContract;
}
