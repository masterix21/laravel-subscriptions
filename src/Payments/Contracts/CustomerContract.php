<?php

namespace LucaLongo\Subscriptions\Payments\Contracts;

use Illuminate\Foundation\Auth\User;

interface CustomerContract
{
    public function customerFindOrNew(User $user): mixed;
}
