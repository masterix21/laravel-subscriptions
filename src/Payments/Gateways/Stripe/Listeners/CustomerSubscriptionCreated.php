<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe\Listeners;

use Illuminate\Support\Carbon;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Payments\Gateways\Stripe\Customer;
use Stripe\Event;
use Stripe\Subscription as StripeSubscription;

class CustomerSubscriptionCreated implements StripeEventHandle
{
    public function handle(Event $event): bool
    {
        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $event->data->object;

        $subscriber = app(Customer::class)->findSubscriberByCustomer($stripeSubscription->customer);

        $plan = app(PlanContract::class)::query()
            ->where('meta->stripe_id', $stripeSubscription->plan->id)
            ->firstOrFail();

        return $subscriber->subscribe(
            plan: $plan,
            status: SubscriptionStatus::from($stripeSubscription->status),
            data: [
                'payment_provider' => 'stripe',
                'payment_provider_reference' => $stripeSubscription->id,
                'next_billing_at' => Carbon::make($stripeSubscription->current_period_end),
                'price' => $stripeSubscription->plan->amount / 100,
                'trial_ends_at' => Carbon::make($stripeSubscription->trial_end),
            ]
        ) instanceof SubscriptionContract;
    }
}
