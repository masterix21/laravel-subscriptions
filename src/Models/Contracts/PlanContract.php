<?php

namespace LucaLongo\Subscriptions\Models\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;

interface PlanContract
{
    public function invoiceLabel(): Attribute;
    public function hasTrial(): bool;
    public function hasDuration(): bool;
    public function hasGrace(): bool;
    public function hasInvoiceCycle(): bool;

    public function subscribe(
        SubscriberContract $subscriber,
        SubscriptionStatus $status = SubscriptionStatus::ACTIVE,
        bool $autoRenew = true,
        array $data = []
    ): SubscriptionContract;
}
