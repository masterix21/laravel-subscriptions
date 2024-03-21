<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use LucaLongo\Subscriptions\Enums\DurationInterval;

class Plan extends Model
{
    protected function casts(): array
    {
        return [
            'duration' => 'int',
            'duration_interval' => DurationInterval::class,
            'price' => 'decimal',
            'grace_days' => 'int',
            'meta' => AsArrayObject::class,
        ];
    }
}
