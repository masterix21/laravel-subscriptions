<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LucaLongo\Subscriptions\Enums\DurationInterval;
use LucaLongo\Subscriptions\Models\Concerns\HasCode;

class Plan extends Model
{
    use HasCode;

    public $guarded = [];

    public $appends = [
        'invoice_label',
        'has_trial',
        'has_duration',
        'has_grace',
        'has_invoice_cycle',
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
            'invoice_period' => 'int',
            'invoice_interval' => DurationInterval::class,
            'grace_period' => 'int',
            'grace_interval' => DurationInterval::class,
            'meta' => AsArrayObject::class,
        ];
    }

    public function invoiceLabel(): Attribute
    {
        return Attribute::get(fn () => trans_choice('subscriptions::subscriptions.cycle', $this->invoice_period, [
            'value' => $this->invoice_period,
            'single_interval' => $this->invoice_interval?->labelSingular(),
            'many_interval' => $this->invoice_interval?->label(),
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
            ->using(config('subscriptions.models.plan_feature'))
            ->withTimestamps();
    }

    public function hasTrial(): Attribute
    {
        return Attribute::get(fn () => filled($this->trial_period) && filled($this->trial_interval));
    }

    public function hasDuration(): Attribute
    {
        return Attribute::get(fn () => filled($this->duration_period) && filled($this->duration_interval));
    }

    public function hasGrace(): Attribute
    {
        return Attribute::get(fn () => filled($this->grace_period) && filled($this->grace_interval));
    }

    public function hasInvoiceCycle(): Attribute
    {
        return Attribute::get(fn () => filled($this->invoice_period) && filled($this->invoice_interval));
    }

    public function activeScope(Builder $builder): Builder
    {
        return $builder->where('enabled', true);
    }

    public function inactiveScope(Builder $builder): Builder
    {
        return $builder->where('enabled', false);
    }

    public function visibleScope(Builder $builder): Builder
    {
        return $builder->where('visible', true);
    }

    public function invisibleScope(Builder $builder): Builder
    {
        return $builder->where('visible', false);
    }
}
