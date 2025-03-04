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

        return $subscription
            ->update([
                'ends_at' => Carbon::createFromTimestampUTC($stripeSubscription->cancel_at),
                'next_billing_at' => null,
                'grace_starts_at' => null,
                'grace_ends_at' => null,
            ]);
    }
}
