<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe;

use Illuminate\Http\Request;
use LucaLongo\Subscriptions\Payments\Contracts\WebHookHandlerContract;
use LucaLongo\Subscriptions\Payments\Gateways\Stripe\Listeners\CustomerDeleted;
use LucaLongo\Subscriptions\Payments\Gateways\Stripe\Listeners\CustomerSubscriptionCreated;
use LucaLongo\Subscriptions\Payments\Gateways\Stripe\Listeners\CustomerSubscriptionDeleted;
use LucaLongo\Subscriptions\Payments\Gateways\Stripe\Listeners\CustomerSubscriptionUpdated;
use LucaLongo\Subscriptions\Payments\Gateways\Stripe\Listeners\InvoicePaymentSucceeded;
use Stripe\Webhook;

class WebHookHandler implements WebHookHandlerContract
{
    public function webHookHandler(Request $request): bool
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('services.stripe.webhook_secret')
            );

            return match ($event->type) {
                'customer.subscription.created' => (new CustomerSubscriptionCreated)->handle($event),
                'customer.subscription.updated' => (new CustomerSubscriptionUpdated)->handle($event),
                'customer.subscription.deleted' => (new CustomerSubscriptionDeleted)->handle($event),
                'customer.deleted' => (new CustomerDeleted)->handle($event),
                // 'invoice.payment.action.required' => throw new \Exception('Not implemented'),
                default => true,
            };
        } catch (\Exception $e) {
            return false;
        }
    }
}
