<?php

namespace LucaLongo\Subscriptions\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 *
 * @property string $code
 * @property string $name
 */
trait HasCode
{
    public static function bootHasCode(): void
    {
        static::saving(function (self $model) {
            $model->code ??= str($model->name)->slug();
        });
    }
}
