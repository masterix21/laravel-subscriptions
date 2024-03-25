<?php

namespace LucaLongo\Subscriptions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use LucaLongo\Subscriptions\Models\Subscription;

class SubscriptionUnrevoked
{
    use Dispatchable;

    public function __construct(public Subscription $subscription)
    {
    }
}
