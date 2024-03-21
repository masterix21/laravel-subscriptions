<?php

// config for LucaLongo/Subscriptions
return [
    'models' => [
        'plan' => \LucaLongo\Subscriptions\Models\Plan::class,
        'feature' => \LucaLongo\Subscriptions\Models\Feature::class,
        'plan_feature' => \LucaLongo\Subscriptions\Models\PlanFeature::class,
        'subscription' => \LucaLongo\Subscriptions\Models\Subscription::class,
        'subscription_payment' => \LucaLongo\Subscriptions\Models\SubscriptionPayment::class,
    ],
];
