<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property string $status
 * @property string|null $payment_provider
 * @property string|null $payment_provider_reference
 * @property string $subscriber_type
 * @property int $subscriber_id
 * @property int $plan_id
 * @property Carbon|null $ends_at
 * @property Carbon|null $next_billing_at
 * @property string|null $price
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $grace_ends_at
 * @property Carbon|null $revoked_at
 * @property string|null $note
 * @property ArrayObject|null $meta
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $paid_at
 */
class SubscriptionPayment extends Model
{
    public $guarded = [];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'meta' => AsArrayObject::class,
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(config('subscriptions.models.subscription'));
    }
}
