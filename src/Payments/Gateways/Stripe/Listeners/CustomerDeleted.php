<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe\Listeners;

use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Payments\Gateways\Stripe\Customer;

class CustomerDeleted implements StripeEventHandle
{
    public function handle($event): bool
    {
        $subscriber = app(Customer::class)->findSubscriberByCustomer($event->data->object);

        $subscriber->activeSubscriptions()->each(function (SubscriptionContract $subscription) {
            $subscription->cancel(now());
        });

        return $subscriber->update(['meta->stripe_id' => null]);
    }
}
