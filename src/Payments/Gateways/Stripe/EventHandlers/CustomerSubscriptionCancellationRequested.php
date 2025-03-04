<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe\EventHandlers;

use Carbon\Carbon;
use LucaLongo\Subscriptions\Models\Subscription;
use Stripe\Event;

class CustomerSubscriptionCancellationRequested implements StripeEventHandle
{
    public function handle(Event $event): bool
    {
        /** @var \Stripe\Subscription $stripeSubscription */
        $stripeSubscription = $event->data->object;

        $subscription = Subscription::query()
            ->where('payment_provider', 'stripe')
            ->where('payment_provider_reference', $stripeSubscription->id)
            ->firstOrFail();

        $subscription->cancel(Carbon::createFromTimestampUTC($stripeSubscription->cancel_at));
    }
}
