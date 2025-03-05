<?php

namespace LucaLongo\Subscriptions\Payments\Gateways;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Payments\Contracts\CreateSubscriptionContract;
use LucaLongo\Subscriptions\Payments\Contracts\CustomerContract;
use LucaLongo\Subscriptions\Payments\Contracts\GatewayContract;
use LucaLongo\Subscriptions\Payments\Contracts\WebHookHandlerContract;
use LucaLongo\Subscriptions\Payments\Gateways\Stripe\CreateSubscription;
use LucaLongo\Subscriptions\Payments\Gateways\Stripe\Customer;
use LucaLongo\Subscriptions\Payments\Gateways\Stripe\WebHookHandler;
use Stripe\StripeClient;

class StripeGateway implements CreateSubscriptionContract, CustomerContract, GatewayContract, WebHookHandlerContract
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * @return StripeClient
     */
    public function client(): mixed
    {
        return $this->stripe;
    }

    public function subscribe(
        PlanContract $plan,
        SubscriberContract $subscriber,
        string $successUrl,
        string $cancelUrl,
        array $options = []
    ): RedirectResponse {
        return app(CreateSubscription::class)->subscribe($plan, $subscriber, $successUrl, $cancelUrl, $options);
    }

    public function customerFindOrNew(SubscriberContract $subscriber): mixed
    {
        return app(Customer::class)->customerFindOrNew($subscriber);
    }

    public function webHookHandler(Request $request): bool
    {
        return app(WebHookHandler::class)->webHookHandler($request);
    }
}
