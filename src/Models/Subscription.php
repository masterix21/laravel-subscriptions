<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Enums\Duration;
use LucaLongo\Subscriptions\Models\Concerns\ImplementsSubscription;

class Subscription extends Model implements SubscriptionContract
{
    use HasFactory;
    use ImplementsSubscription;

    protected $fillable = [
        'subscriber_id',
        'subscriber_type',
        'plan_id',
        'price',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'grace_ends_at',
        'autorenew',
        'billing_cycle',
        'consumed_features',
        'custom_features',
        'pending_downgrade',
    ];

    protected $casts = [
        'price' => 'int',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'autorenew' => 'bool',
        'billing_cycle' => Duration::class,
        'consumed_features' => AsArrayObject::class,
        'custom_features' => AsArrayObject::class,
        'pending_downgrade' => AsArrayObject::class,
    ];

    public function subscriber(): MorphTo
    {
        return $this->morphTo();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('subscriptions.models.plan'), 'plan_id');
    }
}
