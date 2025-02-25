<?php

namespace LucaLongo\Subscriptions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;

class SubscriptionUpgraded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SubscriptionContract $subscription,
        public PlanContract $newPlan,
    ) {
        // ...
    }
}
