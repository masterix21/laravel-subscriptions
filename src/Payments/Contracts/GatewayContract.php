<?php

namespace LucaLongo\Subscriptions\Payments\Contracts;

interface GatewayContract
{
    public function client(): mixed;
}
