<?php

namespace LucaLongo\Subscriptions\Tests\TestClasses;

use Illuminate\Database\Eloquent\Casts\Attribute;
use LucaLongo\Subscriptions\Models\Concerns\HasSubscriptions;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;

class User extends \Illuminate\Foundation\Auth\User implements SubscriberContract
{
    use HasSubscriptions;

    protected $guarded = [];

    public function displayLabel(): Attribute
    {
        return Attribute::get(fn () => $this->name);
    }

    public function customerName(): string
    {
        return $this->name ?? '';
    }

    public function customerEmail(): string
    {
        return $this->email ?? '';
    }

    public function customerUniqueIdentifierKey(): string
    {
        return $this->getKeyName();
    }

    public function customerUniqueIdentifier(): string
    {
        return (string) $this->getKey();
    }
}
