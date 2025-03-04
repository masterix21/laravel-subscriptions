<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe\EventHandlers;

use Illuminate\Support\Carbon;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Models\Plan;
use LucaLongo\Subscriptions\Models\Subscription;
use LucaLongo\Subscriptions\Payments\Gateways\StripeGateway;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Subscription as StripeSubscription;

class CheckoutSessionCompleted implements StripeEventHandle
{
    public function handle(Event $event): bool
    {
        /** @var Session $session */
        $session = $event->data->object;

        $planId = $session->metadata?->plan_id;

        $subscriberKeyName = app(SubscriberContract::class)->getForeignKey();
        $subscriberId = $session->metadata?->$subscriberKeyName;

        // $customerId = $session->customer;
        $subscriptionId = $session->subscription;

        if (! $planId) {
            throw new \Exception('Missing plan_id');
        }

        if (! $subscriberId) {
            throw new \Exception('Missing '.$subscriberKeyName);
        }

        $plan = Plan::findOrFail($planId);
        $subscriber = app(SubscriberContract::class)->findOrFail($subscriberId);

        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = app(StripeGateway::class)->client()->subscriptions->retrieve($subscriptionId);

        if ($stripeSubscription->status !== 'active') {
            return false;
        }

        $subscription = Subscription::query()
            ->where('payment_provider', 'stripe')
            ->where('payment_provider_reference', $subscriptionId)
            ->firstOrNew();

        $subscription->payment_provider = 'stripe';
        $subscription->payment_provider_reference = $subscriptionId;
        $subscription->price = $plan->price;

        $subscription->plan()->associate($plan);
        $subscription->subscriber()->associate($subscriber);

        $this->evaluateTrialPeriod($stripeSubscription, $subscription);
        $this->evaluteValidityPeriod($stripeSubscription, $subscription);

        return $subscription->save();
    }

    protected function evaluateTrialPeriod(StripeSubscription $stripeSubscription, Subscription $subscription): void
    {
        if (! $stripeSubscription->trial_end && ! $subscription->trial_starts_at) {
            return;
        }

        $trialStartsAt = Carbon::createFromTimestampUTC($stripeSubscription->trial_start)->toImmutable();
        $trialEndsAt = Carbon::createFromTimestampUTC($stripeSubscription->trial_end)->toImmutable();

        $subscription->trial_starts_at = $trialStartsAt;
        $subscription->trial_ends_at = $trialEndsAt;
    }

    protected function evaluteValidityPeriod(
        StripeSubscription $stripeSubscription,
        Subscription $subscription
    ): void {
        $startsAt = Carbon::createFromTimestampUTC($stripeSubscription->start_date)->toImmutable();

        $subscription->starts_at = $startsAt;

        if ($stripeSubscription->ended_at) {
            $subscription->ends_at = Carbon::createFromTimestampUTC($stripeSubscription->ended_at)->toImmutable();
        }

        $nextBillingAt = Carbon::createFromTimestampUTC($stripeSubscription->current_period_end)->toImmutable();

        $subscription->next_billing_at = $nextBillingAt;
        $subscription->grace_starts_at = $nextBillingAt;
        $subscription->grace_ends_at = $nextBillingAt->addDays(3);
    }
}
