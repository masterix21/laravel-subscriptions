<?php

namespace LucaLongo\Subscriptions\Repositories;

use Illuminate\Database\Eloquent\Collection;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Enums\Duration;
use LucaLongo\Subscriptions\Events\SubscriptionCreated;
use LucaLongo\Subscriptions\Exceptions\NotStackablePlanException;
use LucaLongo\Subscriptions\Exceptions\ReachedMaxStackedPlan;
use LucaLongo\Subscriptions\Repositories\Contracts\PlanRepositoryInterface;

class PlanRepository implements PlanRepositoryInterface
{
    public function all(): Collection
    {
        return app(PlanContract::class)::query()->get();
    }

    public function findById(string $id): ?PlanContract
    {
        return app(PlanContract::class)::query()
            ->firstWhere('id', $id);
    }

    public function findByCode(string $code): ?PlanContract
    {
        return app(PlanContract::class)::query()
            ->firstWhere('code', $code);
    }

    public function create(array $data): PlanContract
    {
        return app(PlanContract::class)::create($data);
    }

    public function update(PlanContract $plan, array $data): bool
    {
        return $plan->update($data);
    }

    public function delete(PlanContract $plan): bool
    {
        return $plan->delete();
    }

    public function subscribe(PlanContract $plan, SubscriberContract $subscriber, array $data): SubscriptionContract
    {
        $currentCount = $subscriber->subscriptions()
            ->where('plan_id', $plan->getKey())
            ->where('ends_at', '>', now())
            ->count();

        if (! $plan->is_stackable && $currentCount > 0) {
            throw new NotStackablePlanException($plan, $subscriber, $data);
        } elseif ($plan->is_stackable && $currentCount >= $plan->stackable_limit) {
            throw new ReachedMaxStackedPlan($plan, $subscriber, $data);
        }

        $billingCycle = Duration::tryFrom($data['billing_cycle'] ?? Duration::MONTHLY->value) ?? Duration::MONTHLY;

        $price = $plan->getPrice($billingCycle, $data['country'] ?? null);

        $subscription = app(SubscriptionContract::class)::create([
            'subscriber_id' => $subscriber->getKey(),
            'subscriber_type' => $subscriber::class,
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
}
