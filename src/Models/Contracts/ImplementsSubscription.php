<?php

namespace LucaLongo\Subscriptions\Models\Contracts;

use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Repositories\Contracts\SubscriptionRepositoryInterface;

trait ImplementsSubscription
{
    public function upgradeTo(PlanContract $newPlan): bool
    {
        return app(SubscriptionRepositoryInterface::class)->upgrade($this, $newPlan);
    }

    public function downgradeTo(PlanContract $newPlan): bool
    {
        return app(SubscriptionRepositoryInterface::class)->downgrade($this, $newPlan);
    }
}
