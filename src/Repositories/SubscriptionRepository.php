<?php

namespace LucaLongo\Subscriptions\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
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
        $subscription = app(SubscriptionContract::class)::query()->create([
            'subscriber_id' => $subscriber->getKey(),
            'subscriber_type' => $subscriber::class,
            'plan_id' => $plan->getKey(),
            'starts_at' => now(),
            'ends_at' => now()->addMonths($data['duration'] ?? 1),
            'trial_ends_at' => isset($data['trial_days']) ? now()->addDays($data['trial_days']) : null,
            'grace_ends_at' => isset($data['grace_days']) ? now()->addDays($data['grace_days']) : null,
            'custom_features' => $data['custom_features'] ?? null,
            'meta' => $data['meta'] ?? null,
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
}
