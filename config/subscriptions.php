<?php

use Illuminate\Foundation\Auth\User;
use LucaLongo\Subscriptions\Models\Feature;
use LucaLongo\Subscriptions\Models\Plan;
use LucaLongo\Subscriptions\Models\PlanFeature;
use LucaLongo\Subscriptions\Models\Subscription;
use LucaLongo\Subscriptions\Payments\Gateways\StripeGateway;

return [
    'subscriber' => User::class,
    'payment_gateway' => StripeGateway::class,

    'models' => [
        'plan' => Plan::class,
        'feature' => Feature::class,
        'plan_feature' => PlanFeature::class,
        'subscription' => Subscription::class,
    ],
];
