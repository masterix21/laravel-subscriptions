<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe\EventHandlers;

use Stripe\Event;

class CustomerSubscriptionUpdated implements StripeEventHandle
{
    public function handle(Event $event): bool
    {
        /** @var \Stripe\Subscription $stripeSubscription */
        $stripeSubscription = $event->data->object;

        if ($stripeSubscription->cancellation_details?->reason === 'cancellation_requested') {
            return (new CustomerSubscriptionCancellationRequested())->handle($event);
        }

        return true;
    }
}
