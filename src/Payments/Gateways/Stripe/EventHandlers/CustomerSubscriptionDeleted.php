<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe\EventHandlers;

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

        return $subscription
            ->update([
                'ends_at' => Carbon::createFromTimestampUTC($stripeSubscription->ended_at),
                'grace_ends_at' => Carbon::createFromTimestampUTC($stripeSubscription->canceled_at),
                'revoked_at' => Carbon::createFromTimestampUTC($stripeSubscription->canceled_at),
            ]);
    }
}
