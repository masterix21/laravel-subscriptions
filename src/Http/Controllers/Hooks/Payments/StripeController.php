<?php

namespace LucaLongo\Subscriptions\Http\Controllers\Hooks\Payments;

use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeController
{
    protected StripeClient $stripe;

    public function __invoke(Request $request): bool
    {
        return app('paymentGateway')->webHookHandler($request);
    }
}
