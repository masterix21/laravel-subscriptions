<?php

namespace LucaLongo\Subscriptions\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;

interface Subscriber
{
    public function getKey();

    public function label(): Attribute;
}
