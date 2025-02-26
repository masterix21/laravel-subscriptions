<?php

namespace LucaLongo\Subscriptions\Exceptions;

use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriberContract;

class NotStackablePlanException extends \Exception
{
    public function __construct(PlanContract $plan, SubscriberContract $subscriber, array $data)
    {
        parent::__construct("Plan not stackable for the specified subscriber");
    }
}
