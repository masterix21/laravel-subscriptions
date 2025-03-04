<?php

namespace LucaLongo\Subscriptions\Models\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;

interface PlanContract
{
    public function invoiceLabel(): Attribute;

    public function hasTrial(): Attribute;

    public function hasDuration(): Attribute;

    public function hasGrace(): Attribute;

    public function hasInvoiceCycle(): Attribute;

    public function subscribe(
        SubscriberContract $subscriber,
        SubscriptionStatus $status = SubscriptionStatus::ACTIVE,
        bool $autoRenew = true,
        array $data = []
    ): SubscriptionContract;
}
