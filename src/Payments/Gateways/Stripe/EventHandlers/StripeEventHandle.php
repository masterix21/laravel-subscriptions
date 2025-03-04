<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe\EventHandlers;

use Stripe\Event;

interface StripeEventHandle
{
    public function handle(Event $event): bool;
}
