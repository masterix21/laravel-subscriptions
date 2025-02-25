<?php

namespace LucaLongo\Subscriptions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;

class GracePeriodExpired
{
    use Dispatchable, SerializesModels;

    public function __construct(public SubscriptionContract $subscription)
    {
        // ...
    }
}
