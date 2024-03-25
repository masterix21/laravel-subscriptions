<?php

namespace LucaLongo\Subscriptions\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;

interface Subscribable
{
    public function getKey();

    public function label(): Attribute;
}
