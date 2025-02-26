<?php

namespace LucaLongo\Subscriptions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Enums\Duration;

class SubscriptionDowngraded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SubscriptionContract $subscription,
        public PlanContract $newPlan,
        public Duration $newBillingCycle,
    ) {
        // ...
    }
}
