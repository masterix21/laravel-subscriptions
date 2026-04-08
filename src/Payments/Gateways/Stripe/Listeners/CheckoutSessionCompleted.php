<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;
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

        /** @var Model&SubscriberContract $subscriberModel */
        $subscriberModel = app(SubscriberContract::class);
        $subscriberKeyName = $subscriberModel->getForeignKey();
        $subscriberId = $session->metadata?->$subscriberKeyName;

        // $customerId = $session->customer;
        $subscriptionId = $session->subscription;

        if (! $planId) {
            throw new \Exception('Missing plan_id');
        }

        if (! $subscriberId) {
            throw new \Exception('Missing '.$subscriberKeyName);
        }

        /** @var Model&PlanContract $plan */
        $plan = app(PlanContract::class)::findOrFail($planId);

        /** @var Model&SubscriberContract $subscriber */
        $subscriber = $subscriberModel::findOrFail($subscriberId);

        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = app(StripeGateway::class)->client()->subscriptions->retrieve($subscriptionId);

        if ($stripeSubscription->status !== 'active') {
            return false;
        }

        return $subscriber->subscribe($plan, data: [
            'payment_provider' => 'stripe',
            'payment_provider_reference' => $subscriptionId,
            'price' => $session->amount_total / 100,
            'trial_ends_at' => Carbon::make($stripeSubscription->trial_end),
            'next_billing_at' => Carbon::make($stripeSubscription->current_period_end),
        ]) instanceof SubscriptionContract;
    }
}
