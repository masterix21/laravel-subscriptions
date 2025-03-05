<?php

namespace LucaLongo\Subscriptions\Models\Contracts;

use LucaLongo\Subscriptions\Enums\SubscriptionStatus;

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
