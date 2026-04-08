<?php

namespace LucaLongo\Subscriptions\Models\Contracts;

use Illuminate\Database\Eloquent\Casts\ArrayObject;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;

/**
 * @property ArrayObject|null $meta
 */
interface SubscriberContract
{
    public function customerName(): string;

    public function customerEmail(): string;

    public function customerUniqueIdentifierKey(): string;

    public function customerUniqueIdentifier(): string;

    public function subscribe(
        PlanContract $plan,
        SubscriptionStatus $status = SubscriptionStatus::ACTIVE,
        bool $autoRenew = true,
        array $data = []
    ): SubscriptionContract;
}
