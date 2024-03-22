<?php

namespace LucaLongo\Subscriptions\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/** @mixin Model */
trait HasSubscriptions
{
    public function subscriptions(): MorphToMany
    {
        return $this->morphToMany(config('subscriptions.models.subscription'), 'subscriber');
    }

    public function activeLicenses(): MorphToMany
    {
        return $this->subscriptions()->active();
    }
}
