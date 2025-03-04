<?php

namespace LucaLongo\Subscriptions\Http\Controllers\Hooks\Payments;

use Illuminate\Http\Request;
use Stripe\StripeClient;

class StripeController
{
    protected StripeClient $stripe;

    public function __invoke(Request $request): bool
    {
        return app('paymentGateway')->webHookHandler($request);
    }
}
