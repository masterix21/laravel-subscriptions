<?php

namespace LucaLongo\Subscriptions\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LucaLongo\Subscriptions\Subscriptions
 */
class Subscriptions extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \LucaLongo\Subscriptions\Subscriptions::class;
    }
}
