<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LucaLongo\Subscriptions\Models\Concerns\HasCode;

class Feature extends Model
{
    use HasCode;

    public $guarded = [];

    protected function casts(): array
    {
        return [
            'meta' => AsArrayObject::class,
        ];
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(config('subscriptions.models.feature'), 'plan_feature')
            ->using(config('subscriptions.models.plan_feature'))
            ->withTimestamps();
    }
}
