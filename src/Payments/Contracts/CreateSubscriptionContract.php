<?php

namespace LucaLongo\Subscriptions\Payments\Contracts;

use Illuminate\Http\RedirectResponse;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;

interface CreateSubscriptionContract
{
    public function subscribe(
        PlanContract $plan,
        SubscriberContract $subscriber,
        string $successUrl,
        string $cancelUrl,
        array $options = []
    ): RedirectResponse;
}
