<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Subscription extends Model
{
    use HasUuids;
    use SoftDeletes;

    public $guarded = [];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'next_billing_at' => 'datetime',
            'price' => 'decimal:2',
            'trial_starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'grace_starts_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'revoked_at' => 'datetime',
            'meta' => AsArrayObject::class,
        ];
    }

    public function subscriber(): MorphTo
    {
        return $this->morphTo();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('subscriptions.models.plan'));
    }

    public function payments(): HasMany
    {
        return $this->hasMany(config('subscriptions.models.subscription_payment'));
    }

    public function features(): HasManyThrough
    {
        /** @var Model $featureModel */
        $featureModel = resolve(config('subscriptions.models.feature'));

        /** @var Model $planModel */
        $planModel = resolve(config('subscriptions.models.plan'));

        return $this->hasManyThrough(
            $featureModel::class,
            config('subscriptions.models.plan_feature'),
            $planModel->getForeignKey(),
            $featureModel->getKeyName(),
            $planModel->getForeignKey(),
            $featureModel->getForeignKey(),
        );
    }

    public function isRevokable(): Attribute
    {
        return Attribute::get(fn () => $this->is_active);
    }

    public function isRevoked(): Attribute
    {
        return Attribute::get(fn (): bool => filled($this->revoked_at));
    }

    public function isTrialPeriod(): Attribute
    {
        return Attribute::get(function () {
            if ($this->is_revoked) {
                return false;
            }

            if ($this->trial_starts_at && $this->trial_ends_at) {
                return now()->isBetween($this->trial_starts_at, $this->trial_ends_at);
            }

            if ($this->trial_ends_at) {
                return now()->isBefore($this->trial_ends_at);
            }

            return false;
        });
    }

    public function isGracePeriod(): Attribute
    {
        return Attribute::get(function () {
            if ($this->is_revoked) {
                return false;
            }

            if ($this->grace_starts_at && $this->grace_ends_at) {
                return now()->isBetween($this->grace_starts_at, $this->grace_ends_at);
            }

            if ($this->grace_ends_at) {
                return now()->isBefore($this->grace_ends_at);
            }

            return false;
        });
    }

    public function isActive(): Attribute
    {
        return Attribute::get(function () {
            if ($this->is_revoked) {
                return false;
            }

            if (now()->isBefore($this->starts_at)) {
                return $this->is_trial_period;
            }

            if ($this->ends_at && now()->isAfter($this->ends_at)) {
                return $this->is_grace_period;
            }

            return true;
        });
    }

    public function scopeActive(Builder $query): void
    {
        $query->where(fn (Builder $query) => $query
            ->whereNull('revoked_at')
            ->where(function (Builder $query) {
                $query
                    ->where(fn (Builder $query) => $query
                        ->where('starts_at', '<=', now())
                        ->where(fn (Builder $query) => $query
                            ->whereNull('ends_at')
                            ->orWhere('ends_at', '>=', now())))
                    ->orWhere(fn (Builder $query) => $query
                        ->where('trial_starts_at', '<=', now())
                        ->where('trial_ends_at', '>=', now()))
                    ->orWhere(fn (Builder $query) => $query
                        ->where('grace_starts_at', '<=', now())
                        ->where('grace_ends_at', '>=', now()));
            })
        );
    }

    public function hasFeature(string $feature): bool
    {
        return $this->features->contains('code', $feature);
    }

    public function hasAnyFeature(Collection $features): bool
    {
        return $this->features->pluck('code')->containsAny($features);
    }

    public function hasAllFeature(Collection $features): bool
    {
        return $this->features->pluck('code')->containsAll($features);
    }
}
