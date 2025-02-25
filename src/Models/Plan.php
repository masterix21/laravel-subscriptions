<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Enums\PlanRelationType;

class Plan extends Model implements PlanContract
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'pricing',
        'features',
        'trial_days',
        'grace_days',
        'is_stackable',
        'stackable_limit',
        'meta',
    ];

    protected $casts = [
        'pricing' => AsArrayObject::class,
        'features' => AsArrayObject::class,
        'is_stackable' => 'boolean',
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
