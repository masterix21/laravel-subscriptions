<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe\Listeners;

use Stripe\Event;

interface StripeEventHandle
{
    public function handle(Event $event): bool;
}
