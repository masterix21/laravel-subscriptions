<?php

namespace LucaLongo\Subscriptions\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use LucaLongo\Subscriptions\Events\SubscriptionCreated;
use LucaLongo\Subscriptions\Models\Plan;
use LucaLongo\Subscriptions\Models\Subscription;
use LucaLongo\Subscriptions\Models\SubscriptionPayment;

class CreateSubscription
{
    /**
     * @param  SubscriptionPayment[]  $payments
     */
    public function execute(
        Plan $plan,
        Model $subscriber,
        ?Carbon $startsAt = null,
        ?string $note = null,
        array $meta = [],
    ): Subscription {
        $startsAt ??= now();
        $endsAt = null;

        $trialStartsAt = null;
        $trialEndsAt = null;

        $graceStartsAt = null;
        $graceEndsAt = null;

        if ($plan->has_trial) {
            $trialStartsAt = $startsAt->clone();
            $trialEndsAt = $trialStartsAt->clone()->add($plan->trial_period, $plan->trial_interval);

            $startsAt = $trialEndsAt->clone();
        }

        if ($plan->has_duration) {
            $endsAt = $startsAt->clone()->add($plan->duration_period, $plan->duration_interval);

            if ($plan->has_grace) {
                $graceStartsAt = $endsAt->clone();
                $graceEndsAt = $graceStartsAt->clone()->add($plan->grace_period, $plan->grace_interval);
            }
        }

        return tap(
            value: Subscription::create([
                'subscriber_id' => $subscriber->getKey(),
                'subscriber_type' => $subscriber::class,
                'plan_id' => $plan->getKey(),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'price' => $plan->price,
                'trial_starts_at' => $trialStartsAt,
                'trial_ends_at' => $trialEndsAt,
                'grace_starts_at' => $graceStartsAt,
                'grace_ends_at' => $graceEndsAt,
                'revoked_at' => null,
                'note' => $note,
                'meta' => $meta
            ]),
            callback: fn (Subscription $subscription) => event(new SubscriptionCreated($subscription))
        );
    }
}