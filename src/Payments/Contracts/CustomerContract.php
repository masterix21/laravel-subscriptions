<?php

namespace LucaLongo\Subscriptions\Payments\Contracts;

use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;

interface CustomerContract
{
    public function customerFindOrNew(SubscriberContract $subscriber): mixed;
}
