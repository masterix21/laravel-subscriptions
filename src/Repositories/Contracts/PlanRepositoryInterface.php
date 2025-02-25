<?php

namespace LucaLongo\Subscriptions\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use LucaLongo\Subscriptions\Contracts\PlanContract;

interface PlanRepositoryInterface
{
    public function all(): Collection;

    public function findByCode(string $code): ?PlanContract;

    public function create(array $data): PlanContract;

    public function update(PlanContract $plan, array $data): bool;

    public function delete(PlanContract $plan): bool;
}
