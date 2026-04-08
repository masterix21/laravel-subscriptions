<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LucaLongo\Subscriptions\Actions\Plans\SubscribePlan;
use LucaLongo\Subscriptions\Enums\DurationInterval;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;
use LucaLongo\Subscriptions\Models\Concerns\HasCode;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;

/**
 * @property int $id
 * @property bool $enabled
 * @property bool $hidden
 * @property string|null $subscribable_type
 * @property int|null $subscribable_id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $starts_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property int|null $duration_period
 * @property DurationInterval|null $duration_interval
 * @property string $price
 * @property int|null $trial_period
 * @property DurationInterval|null $trial_interval
 * @property int|null $grace_period
 * @property DurationInterval|null $grace_interval
 * @property \Illuminate\Database\Eloquent\Casts\ArrayObject|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $invoice_period
 * @property-read DurationInterval|null $invoice_interval
 * @property-read string $invoice_label
 */
class Plan extends Model implements PlanContract
{
    use HasCode;

    public $guarded = [];

    public $appends = [
        'invoice_label',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'bool',
            'hidden' => 'bool',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'duration_period' => 'int',
            'duration_interval' => DurationInterval::class,
            'price' => 'decimal:2',
            'trial_period' => 'int',
            'trial_interval' => DurationInterval::class,
            'grace_period' => 'int',
            'grace_interval' => DurationInterval::class,
            'meta' => AsArrayObject::class,
        ];
    }

    public function invoiceLabel(): Attribute
    {
        return Attribute::get(fn () => trans_choice('subscriptions::subscriptions.cycle', $this->invoice_period, [
            'value' => $this->duration_period,
            'single_interval' => $this->duration_interval?->labelSingular(),
            'many_interval' => $this->duration_interval?->label(),
        ]));
    }

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * It's required by Filament to store features using a Repeater
     */
    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function features(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                related: config('subscriptions.models.feature'),
                table: 'plan_feature',
            )
            ->withPivot([
                'max_usage',
            ])
            ->using(config('subscriptions.models.plan_feature'))
            ->withTimestamps();
    }

    public function hasTrial(): bool
    {
        return filled($this->trial_period) && filled($this->trial_interval);
    }

    public function hasDuration(): bool
    {
        return filled($this->duration_period) && filled($this->duration_interval);
    }

    public function hasGrace(): bool
    {
        return filled($this->grace_period) && filled($this->grace_interval);
    }

    public function hasInvoiceCycle(): bool
    {
        return filled($this->duration_period) && filled($this->duration_interval);
    }

    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('enabled', true);
    }

    public function scopeInactive(Builder $builder): Builder
    {
        return $builder->where('enabled', false);
    }

    public function scopeVisible(Builder $builder): Builder
    {
        return $builder->where('hidden', false);
    }

    public function scopeInvisible(Builder $builder): Builder
    {
        return $builder->where('hidden', true);
    }

    public function subscribe(
        SubscriberContract $subscriber,
        SubscriptionStatus $status = SubscriptionStatus::ACTIVE,
        bool $autoRenew = true,
        array $data = []
    ): SubscriptionContract {
        return app(SubscribePlan::class)->subscribe($this, $subscriber, $status, $autoRenew, $data);
    }
}
