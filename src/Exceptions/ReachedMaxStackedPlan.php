<?php

namespace LucaLongo\Subscriptions\Exceptions;

class ReachedMaxStackedPlan extends \Exception
{
    public function __construct(
        \LucaLongo\Subscriptions\Contracts\PlanContract $plan,
        \LucaLongo\Subscriptions\Contracts\SubscriberContract $subscriber,
        array $data
    ) {
        parent::__construct('Maximum number of stacked plans reached for the specified subscriber');
    }
}
