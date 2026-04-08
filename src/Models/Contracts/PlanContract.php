<?php

namespace LucaLongo\Subscriptions\Models\Contracts;

use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use LucaLongo\Subscriptions\Enums\DurationInterval;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int|null $duration_period
 * @property DurationInterval|null $duration_interval
 * @property string $price
 * @property int|null $trial_period
 * @property DurationInterval|null $trial_interval
 * @property int|null $grace_period
 * @property DurationInterval|null $grace_interval
 * @property ArrayObject|null $meta
 */
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
