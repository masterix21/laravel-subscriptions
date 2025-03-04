<?php

namespace LucaLongo\Subscriptions\Actions\Plans;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;

class SubscribePlan
{
    public function subscribe(
        PlanContract $plan,
        SubscriberContract $subscriber,
        SubscriptionStatus $status = SubscriptionStatus::ACTIVE,
        bool $autoRenew = true,
        array $data = []
    ): SubscriptionContract
    {
        $nextBillingAt = Carbon::make($data['next_billing_at'] ?? null)
            ?: now()->add($plan->duration_period, $plan->duration_interval->value);

        $endsAt = Carbon::make($data['ends_at'] ?? null);
        $trialEndsAt = Carbon::make($data['trial_ends_at'] ?? null);
        $graceEndsAt = Carbon::make($data['grace_ends_at'] ?? null);

        if ($plan->hasTrial()) {
            $trialEndsAt ??= now()->add($plan->trial_period, $plan->trial_interval->value);
        }

        if ($plan->hasGrace()) {
            $graceEndsAt ??= $nextBillingAt->toImmutable()->add($plan->grace_period, $plan->grace_interval->value);
        }

        /** @var Model $subscription */
        $subscription = app(SubscriptionContract::class)::query()
            ->when(
                value: filled($data['payment_provider'] ?? null) && filled($data['payment_provider_reference'] ?? null),
                callback: fn ($query) => $query
                    ->where('payment_provider', $data['payment_provider'])
                    ->where('payment_provider_reference', $data['payment_provider_reference'])
            )->firstOrNew();

        if (! $autoRenew) {
            $endsAt ??= $nextBillingAt;
        }

        $subscription->fill([
                ...$data,
                $plan->getForeignKey() => $plan->getKey(),
                'ends_at' => $endsAt,
                'trial_ends_at' => $trialEndsAt,
                'grace_ends_at' => $graceEndsAt,
                'next_billing_at' => $nextBillingAt,
                'status' => $status,
                'auto_renew' => $autoRenew,
            ]);

        $subscription->subscriber()->associate($subscriber);

        $subscription->save();

        return $subscription;
    }
}
