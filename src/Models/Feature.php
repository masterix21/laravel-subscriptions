<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use LucaLongo\Subscriptions\Models\Concerns\HasCode;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property ArrayObject|null $meta
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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
