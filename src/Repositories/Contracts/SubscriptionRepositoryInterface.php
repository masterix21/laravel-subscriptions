<?php

namespace LucaLongo\Subscriptions\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Enums\Duration;

interface SubscriptionRepositoryInterface
{
    public function all(SubscriberContract $subscriber): Collection;

    public function findActiveBySubscriber(SubscriberContract $subscriber): ?SubscriptionContract;

    public function cancel(SubscriptionContract $subscription): bool;

    public function reactivate(SubscriptionContract $subscription): bool;

    public function canUpgrade(SubscriptionContract $subscription, PlanContract $newPlan, Duration $newBillingCycle): bool;

    public function upgrade(SubscriptionContract $subscription, PlanContract $newPlan, Duration $newBillingCycle, ?Carbon $newEndDate = null): bool;

    public function canDowngrade(SubscriptionContract $subscription, PlanContract $newPlan, Duration $newBillingCycle): bool;

    public function downgrade(SubscriptionContract $subscription, PlanContract $newPlan, Duration $newBillingCycle, ?Carbon $newEndDate = null): bool;

    public function isRenewable(SubscriptionContract $subscription): bool;

    public function renew(SubscriptionContract $subscription, ?Duration $newBillingCycle = null, ?Carbon $newEndDate = null): bool;

    public function enableAutoRenew(SubscriptionContract $subscription): bool;

    public function disableAutoRenew(SubscriptionContract $subscription): bool;

    public function canConsumeFeature(SubscriptionContract $subscription, string $key): bool;

    public function consumeFeature(SubscriptionContract $subscription, string $key, int $amount = 1): bool;

    public function canStackPlan(SubscriptionContract $subscription): bool;
}
