<?php

namespace LucaLongo\Subscriptions\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Enums\Duration;
use LucaLongo\Subscriptions\Events\SubscriptionCancelled;
use LucaLongo\Subscriptions\Events\SubscriptionCreated;
use LucaLongo\Subscriptions\Events\SubscriptionDowngraded;
use LucaLongo\Subscriptions\Events\SubscriptionUpgraded;
use LucaLongo\Subscriptions\Repositories\Contracts\SubscriptionRepositoryInterface;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function all(SubscriberContract $subscriber): Collection
    {
        return app(SubscriptionContract::class)::query()
            ->where('subscriber_id', $subscriber->getKey())
            ->where('subscriber_type', $subscriber::class)
            ->get();
    }

    public function findActiveBySubscriber(SubscriberContract $subscriber): ?SubscriptionContract
    {
        return app(SubscriptionContract::class)::query()
            ->where('subscriber_id', $subscriber->getKey())
            ->where('subscriber_type', $subscriber::class)
            ->where('ends_at', '>', now())
            ->first();
    }

    public function subscribe(SubscriberContract $subscriber, PlanContract $plan, array $data): SubscriptionContract
    {
        $billingCycle = Duration::tryFrom($data['billing_cycle'] ?? Duration::MONTHLY->value) ?? Duration::MONTHLY;

        $price = $plan->getPrice($billingCycle, $data['country'] ?? null);

        $subscription = app(SubscriptionContract::class)::create([
            'subscriber_id' => $subscriber->getKey(),
            'subscriber_type' => get_class($subscriber),
            'plan_id' => $plan->getKey(),
            'billing_cycle' => $billingCycle,
            'starts_at' => now(),
            'ends_at' => now()->addMonths($billingCycle->toMonths()),
            'autorenew' => $data['autorenew'] ?? true,
            'price' => $price, // Prezzo in centesimi
        ]);

        event(new SubscriptionCreated($subscription));

        return $subscription;
    }

    public function cancel(SubscriptionContract $subscription): bool
    {
        $updated = $subscription->update(['ends_at' => now()]);

        if ($updated) {
            event(new SubscriptionCancelled($subscription));
        }

        return $updated;
    }

    public function canUpgrade(SubscriptionContract $subscription, PlanContract $newPlan): bool
    {
        return true;
    }

    public function upgrade(SubscriptionContract $subscription, PlanContract $newPlan, ?Carbon $newEndDate = null): bool
    {
        if (! $this->canUpgrade($subscription, $newPlan)) {
            return false;
        }

        $subscription->update([
            'plan_id' => $newPlan->getKey(),
            'ends_at' => $newEndDate ?: $subscription->ends_at,
        ]);

        event(new SubscriptionUpgraded($subscription, $newPlan));

        return true;
    }

    public function canDowngrade(SubscriptionContract $subscription, PlanContract $newPlan): bool
    {
        return true;
    }

    public function downgrade(SubscriptionContract $subscription, PlanContract $newPlan, ?Carbon $newEndDate = null): bool
    {
        if (! $this->canDowngrade($subscription, $newPlan)) {
            return false;
        }

        $subscription->update([
            'plan_id' => $newPlan->getKey(),
            'ends_at' => $newEndDate ?: $subscription->ends_at,
        ]);

        event(new SubscriptionDowngraded($subscription, $newPlan));

        return true;
    }

    public function isRenewable(SubscriptionContract $subscription): bool
    {
        return $subscription->plan->renewable
            && $subscription->hasExpired()
            && ! $subscription->subscriber->subscriptions()
                ->where('plan_id', $subscription->plan_id)
                ->where('ends_at', '>', now())
                ->exists();
    }

    public function renew(SubscriptionContract $subscription, ?Carbon $newEndDate = null): bool
    {
        if (! $this->isRenewable($subscription)) {
            return false;
        }

        return $subscription->update([
            'starts_at' => now(),
            'ends_at' => $subscription->ends_at->addMonths($subscription->billing_cycle->toMonths()),
            'trial_ends_at' => null,
            'grace_ends_at' => null,
        ]);
    }

    public function enableAutoRenew(SubscriptionContract $subscription): bool
    {
        if ($subscription->autorenew) {
            return true;
        }

        return $subscription->update(['autorenew' => true]);
    }

    public function disableAutoRenew(SubscriptionContract $subscription): bool
    {
        if (! $subscription->autorenew) {
            return true;
        }

        return $subscription->update(['autorenew' => false]);
    }
}
