<?php

namespace LucaLongo\Subscriptions\Repositories;

use Illuminate\Database\Eloquent\Collection;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Repositories\Contracts\PlanRepositoryInterface;

class PlanRepository implements PlanRepositoryInterface
{
    public function all(): Collection
    {
        return app(PlanContract::class)::query()->get();
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
}
