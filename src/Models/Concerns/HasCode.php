<?php

namespace LucaLongo\Subscriptions\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/** @mixin Model */
trait HasCode
{
    public static function bootHasCode(): void
    {
        static::saving(function (self $model) {
            $model->code ??= str($model->name)->slug();
        });
    }
}
