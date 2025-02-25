<?php

namespace LucaLongo\Subscriptions\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;

interface SubscriptionRepositoryInterface
{
    public function all(SubscriberContract $subscriber): Collection;

    public function findActiveBySubscriber(SubscriberContract $subscriber): ?SubscriptionContract;

    public function subscribe(SubscriberContract $subscriber, PlanContract $plan, array $data): SubscriptionContract;

    public function cancel(SubscriptionContract $subscription): bool;

    public function canUpgrade(SubscriptionContract $subscription, PlanContract $newPlan): bool;

    public function upgrade(SubscriptionContract $subscription, PlanContract $newPlan, ?Carbon $newEndDate = null): bool;

    public function canDowngrade(SubscriptionContract $subscription, PlanContract $newPlan): bool;

    public function downgrade(SubscriptionContract $subscription, PlanContract $newPlan, ?Carbon $newEndDate = null): bool;

    public function isRenewable(SubscriptionContract $subscription): bool;

    public function renew(SubscriptionContract $subscription, ?Carbon $newEndDate = null): bool;

    public function enableAutoRenew(SubscriptionContract $subscription): bool;

    public function disableAutoRenew(SubscriptionContract $subscription): bool;
}
