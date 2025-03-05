<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe\Listeners;

use Carbon\Carbon;
use LucaLongo\Subscriptions\Models\Subscription;
use Stripe\Event;

class CustomerSubscriptionDeleted implements StripeEventHandle
{
    public function handle(Event $event): bool
    {
        /** @var \Stripe\Subscription $stripeSubscription */
        $stripeSubscription = $event->data->object;

        $subscription = Subscription::query()
            ->where('payment_provider', 'stripe')
            ->where('payment_provider_reference', $stripeSubscription->id)
            ->firstOrFail();

        return $subscription->cancel(Carbon::createFromTimestampUTC($stripeSubscription->ended_at));
    }
}
