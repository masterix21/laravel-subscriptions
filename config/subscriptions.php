<?php

return [
    'models' => [
        'plan' => \LucaLongo\Subscriptions\Models\Plan::class,
        'subscription' => \LucaLongo\Subscriptions\Models\Subscription::class,
    ],

    'repositories' => [
        'plan' => \LucaLongo\Subscriptions\Repositories\PlanRepository::class,
        'subscription' => \LucaLongo\Subscriptions\Repositories\SubscriptionRepository::class,
    ],
];
