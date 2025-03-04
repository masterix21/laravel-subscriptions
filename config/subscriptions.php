<?php

return [
    'subscriber' => \Illuminate\Foundation\Auth\User::class,
    'payment_gateway' => \LucaLongo\Subscriptions\Payments\Gateways\StripeGateway::class,

    'models' => [
        'plan' => \LucaLongo\Subscriptions\Models\Plan::class,
        'feature' => \LucaLongo\Subscriptions\Models\Feature::class,
        'plan_feature' => \LucaLongo\Subscriptions\Models\PlanFeature::class,
        'subscription' => \LucaLongo\Subscriptions\Models\Subscription::class,
    ],
];
