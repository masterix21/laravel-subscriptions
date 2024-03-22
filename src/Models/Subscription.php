<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use function Symfony\Component\Translation\t;

class Subscription extends Model
{
    use HasUuids;
    use SoftDeletes;

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
            'price' => 'decimal',
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
}
