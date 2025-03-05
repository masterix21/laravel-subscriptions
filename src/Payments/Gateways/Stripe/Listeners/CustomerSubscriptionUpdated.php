<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe\Listeners;

use Carbon\Carbon;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Payments\Gateways\Stripe\Customer;
use Stripe\Event;
use Stripe\Subscription as StripeSubscription;

class CustomerSubscriptionUpdated implements StripeEventHandle
{
    public function handle(Event $event): bool
    {
        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $event->data->object;

        $subscriber = app(Customer::class)->findSubscriberByCustomer($stripeSubscription->customer);

        $subscription = $subscriber->subscriptions()
            ->where('payment_provider', 'stripe')
            ->where('payment_provider_reference', $stripeSubscription->id)
            ->firstOrNew();

        $subscription->status = SubscriptionStatus::from($stripeSubscription->status);

        $subscription->next_billing_at = Carbon::createFromTimestamp($stripeSubscription->current_period_end);

        if ($subscription->status === SubscriptionStatus::INCOMPLETE_EXPIRED) {
            return $subscription->delete();
        }

        if (! $subscription->exists) {
            $subscription->subscriber()->associate($subscriber);

            $subscription->plan()->associate(
                app(PlanContract::class)::firstWhere('meta->stripe_id', $stripeSubscription->plan->id)
            );

            $subscription->payment_provider = 'stripe';
            $subscription->payment_provider_reference = $stripeSubscription->id;
        }

        $subscription->price = $stripeSubscription->items->first()->plan->amount / 100;

        if ($stripeSubscription->trial_end) {
            $trialEnd = Carbon::createFromTimestamp($stripeSubscription->trial_end);

            if (! $subscription->trial_ends_at || $subscription->trial_ends_at->ne($trialEnd)) {
                $subscription->trial_ends_at = $trialEnd;
            }
        }

        if ($stripeSubscription->cancel_at_period_end) {
            $subscription->ends_at = $subscription->onTrial()
                ? $subscription->trial_ends_at
                : Carbon::createFromTimestamp($stripeSubscription->current_period_end);

            $subscription->next_billing_at = null;
        } else if ($stripeSubscription->cancel_at || $stripeSubscription->canceled_at) {
            $subscription->ends_at = Carbon::createFromTimestamp(
                $stripeSubscription->cancel_at ?: $stripeSubscription->canceled_at
            );

            $subscription->next_billing_at = null;
        } else {
            $subscription->ends_at = null;
        }

        return $subscription->save();
    }
}
