<?php

namespace LucaLongo\Subscriptions\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Enums\Duration;
use LucaLongo\Subscriptions\Events\SubscriptionCancelled;
use LucaLongo\Subscriptions\Events\SubscriptionDowngraded;
use LucaLongo\Subscriptions\Events\SubscriptionPendingDowngrade;
use LucaLongo\Subscriptions\Events\SubscriptionReactivated;
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

    public function cancel(SubscriptionContract $subscription): bool
    {
        if (! $subscription->update(['ends_at' => now()])) {
            return false;
        }

        event(new SubscriptionCancelled($subscription));

        return true;
    }

    public function reactivate(SubscriptionContract $subscription): bool
    {
        if ($subscription->ends_at > now()) {
            return false; // Già attiva
        }

        if (! $subscription->update(['ends_at' => now()->addMonths($subscription->billing_cycle->toMonths())])) {
            return false;
        }

        event(new SubscriptionReactivated($subscription));

        return true;
    }

    public function canUpgrade(SubscriptionContract $subscription, PlanContract $newPlan, Duration $newBillingCycle): bool
    {
        if ($subscription->plan_id === $newPlan->getKey() && $subscription->billing_cycle === $newBillingCycle) {
            return false;
        }

        return $newBillingCycle->toDays() > $subscription->billing_cycle->toDays();
    }

    public function upgrade(SubscriptionContract $subscription, PlanContract $newPlan, Duration $newBillingCycle, ?Carbon $newEndDate = null): bool
    {
        if (! $this->canUpgrade($subscription, $newPlan, $newBillingCycle)) {
            return false;
        }

        $newPrice = $newPlan->getPrice($newBillingCycle, $this->subscriber->country ?? null);

        $newEndDate ??= $subscription->ends_at->clone()
            ->addDays($newBillingCycle->toDays() - $subscription->billing_cycle->toDays());

        $subscription->update([
            'plan_id' => $newPlan->getKey(),
            'billing_cycle' => $newBillingCycle ?? $subscription->billing_cycle,
            'ends_at' => $newEndDate ?? $subscription->ends_at,
            'price' => $newPrice ?? $subscription->price,
        ]);

        event(new SubscriptionUpgraded($subscription, $newPlan, $newBillingCycle));

        return true;
    }

    public function canDowngrade(SubscriptionContract $subscription, PlanContract $newPlan, Duration $newBillingCycle): bool
    {
        if ($subscription->plan_id === $newPlan->getKey() && $subscription->billing_cycle === $newBillingCycle) {
            return false;
        }

        return $newBillingCycle->toDays() < $subscription->billing_cycle->toDays();
    }

    public function downgrade(SubscriptionContract $subscription, PlanContract $newPlan, Duration $newBillingCycle, ?Carbon $newEndDate = null): bool
    {
        if (! $this->canDowngrade($subscription, $newPlan, $newBillingCycle)) {
            return false;
        }

        return $subscription->update([
            'pending_downgrade' => [
                'plan_id' => $newPlan->getKey(),
                'billing_cycle' => $newBillingCycle->value,
                'price' => $newPlan->getPrice($newBillingCycle, $subscription->subscriber->country ?? null),
            ],
        ]);

        event(new SubscriptionPendingDowngrade($subscription, $newPlan, $newBillingCycle));

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

    public function renew(SubscriptionContract $subscription, ?Duration $newBillingCycle = null, ?Carbon $newEndDate = null): bool
    {
        if (! $this->isRenewable($subscription)) {
            return false;
        }

        $newPrice = null;

        if ($newBillingCycle && $newBillingCycle !== $subscription->billing_cycle) {
            $newPrice = $subscription->plan->getPrice($newBillingCycle, $this->subscriber->country ?? null);

            $newEndDate ??= now()->addMonths($newBillingCycle->toMonths());
        }

        return $subscription->update([
            'starts_at' => now(),
            'price' => $newPrice ?: $subscription->price,
            'ends_at' => $newEndDate ?: $subscription->ends_at->addMonths($subscription->billing_cycle->toMonths()),
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

    public function canConsumeFeature(SubscriptionContract $subscription, string $key): bool
    {
        $limit = $subscription->plan->getFeature($key);
        $consumed = $subscription->getConsumedFeature($key);

        return $limit === null || $consumed < $limit;
    }

    public function consumeFeature(SubscriptionContract $subscription, string $key, int $amount = 1): bool
    {
        if (! $subscription->canConsumeFeature($key)) {
            return false;
        }

        $consumed = $subscription->consumed_features ?: [];

        data_set($consumed, $key, $subscription->getConsumedFeature($key) + $amount);

        return $subscription->update([
            'consumed_features' => $consumed
        ]);
    }

    public function canStackPlan(SubscriptionContract $subscription): bool
    {
        if (! $subscription->plan->is_stackable) {
            return false;
        }

        $limit = $subscription->plan->stackable_limit;

        $currentCount = $subscription->subscriber->subscriptions()
            ->where('plan_id', $subscription->plan->id)
            ->where('ends_at', '>', now())
            ->count();

        return $limit === null || $currentCount < $limit;
    }
}
