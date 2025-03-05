<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe;

use Illuminate\Http\RedirectResponse;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Payments\Contracts\CreateSubscriptionContract;
use LucaLongo\Subscriptions\Payments\Exceptions\PaymentGatewayUnsupportedByPlan;
use LucaLongo\Subscriptions\Payments\Gateways\StripeGateway;

class CreateSubscription implements CreateSubscriptionContract
{
    public function subscribe(
        PlanContract $plan,
        SubscriberContract $subscriber,
        string $successUrl,
        string $cancelUrl,
        array $options = []
    ): RedirectResponse {
        if (! ($plan->meta['stripe_id'] ?? null)) {
            throw new PaymentGatewayUnsupportedByPlan($plan->name.' does not support Stripe');
        }

        $stripeCustomer = app(Customer::class)->customerFindOrNew($subscriber);

        return redirect()->away(
            app(StripeGateway::class)->client()->checkout->sessions->create([
                'mode' => 'subscription',
                'customer' => $stripeCustomer->id,
                'line_items' => [
                    [
                        'price' => $plan->meta['stripe_id'],
                        'quantity' => 1,
                    ],
                ],
                'metadata' => [
                    $subscriber->customerUniqueIdentifierKey() => $subscriber->customerUniqueIdentifier(),
                    'plan_id' => $plan->getKey(),
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ])->url
        );
    }
}
