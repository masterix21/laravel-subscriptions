<?php

namespace LucaLongo\Subscriptions\Tests\TestClasses;

use Illuminate\Database\Eloquent\Casts\Attribute;
use LucaLongo\Subscriptions\Models\Concerns\HasSubscriptions;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;

class User extends \Illuminate\Foundation\Auth\User implements SubscriberContract
{
    use HasSubscriptions;

    protected $guarded = [];

    public function displayLabel(): Attribute
    {
        return Attribute::get(fn () => $this->name);
    }
}
