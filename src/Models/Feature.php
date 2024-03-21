<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected function casts()
    {
        return [
            'meta' => AsArrayObject::class,
        ];
    }
}
