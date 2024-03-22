<?php

namespace LucaLongo\Subscriptions\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;

interface Subscribable
{
    public function subscribableKey(): Attribute;

    public function subscribableLabel(): Attribute;
}
