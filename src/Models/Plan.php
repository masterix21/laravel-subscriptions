<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Enums\Duration;
use LucaLongo\Subscriptions\Enums\PlanRelationType;
use LucaLongo\Subscriptions\Models\Concerns\ImplementsPlan;

class Plan extends Model implements PlanContract
{
    use HasFactory;
    use ImplementsPlan;

    protected $fillable = [
        'code',
        'name',
        'description',
        'renewable',
        'pricing',
        'features',
        'trial_days',
        'grace_days',
        'is_stackable',
        'stackable_limit',
        'meta',
    ];

    protected $casts = [
        'renewable' => 'boolean',
        'pricing' => AsArrayObject::class,
        'features' => AsArrayObject::class,
        'is_stackable' => 'boolean',
        'stackable_limit' => 'integer',
        'meta' => AsArrayObject::class,
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('subscriptions.models.subscription'));
    }

    public function upgrades(): BelongsToMany
    {
        return $this->belongsToMany(
            static::class,
            'plan_relations',
            'plan_id',
            'related_plan_id'
        )->wherePivot('relation_type', PlanRelationType::UPGRADE->value);
    }

    public function downgrades(): BelongsToMany
    {
        return $this->belongsToMany(
            static::class,
            'plan_relations',
            'plan_id',
            'related_plan_id'
        )->wherePivot('relation_type', PlanRelationType::DOWNGRADE->value);
    }
}
