<?php

namespace LucaLongo\Subscriptions\Tests\TestClasses;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as BaseUser;
use LucaLongo\Subscriptions\Contracts\SubscriberContract;

class User extends BaseUser implements SubscriberContract
{
    protected $guarded = [];

    public function subscriptions(): MorphMany
    {
        return $this->morphMany(config('subscriptions.models.subscription'), 'subscriber');
    }

    public function activeSubscriptions(): MorphMany
    {
        return $this->subscriptions()->where('ends_at', '>', now());
    }
}
