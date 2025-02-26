<?php

namespace LucaLongo\Subscriptions\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Enums\Duration;

interface PlanRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): ?PlanContract;

    public function findByCode(string $code): ?PlanContract;

    public function create(array $data): PlanContract;

    public function update(PlanContract $plan, array $data): bool;

    public function delete(PlanContract $plan): bool;

    public function subscribe(PlanContract $plan, Duration $billingCycle, SubscriberContract $subscriber, array $data): SubscriptionContract;
}
