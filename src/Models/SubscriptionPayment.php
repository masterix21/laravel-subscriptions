<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property string $status
 * @property string|null $payment_provider
 * @property string|null $payment_provider_reference
 * @property string $subscriber_type
 * @property int $subscriber_id
 * @property int $plan_id
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property \Illuminate\Support\Carbon|null $next_billing_at
 * @property string|null $price
 * @property \Illuminate\Support\Carbon|null $trial_ends_at
 * @property \Illuminate\Support\Carbon|null $grace_ends_at
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property string|null $note
 * @property \Illuminate\Database\Eloquent\Casts\ArrayObject|null $meta
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $paid_at
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
