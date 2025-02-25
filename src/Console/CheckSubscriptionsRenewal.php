<?php

namespace LucaLongo\Subscriptions\Console;

use Illuminate\Console\Command;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Events\SubscriptionRenewed;

class CheckSubscriptionsRenewal extends Command
{
    protected $signature = 'subscriptions:check-renewal';
    protected $description = 'Automatically renew renewable subscriptions with auto-renew enabled.';

    public function handle(): void
    {
        $renewableSubscriptions = app(SubscriptionContract::class)::query()
            ->with(['subscriber'])
            ->where('ends_at', '<=', now())
            ->where('autorenew', true)
            ->get();

        /** @var SubscriptionContract $subscription */
        foreach ($renewableSubscriptions as $subscription) {
            if (! $subscription->isRenewable() || ! $subscription->renew()) {
                $subscription->disableAutoRenew();

                continue;
            }

            event(new SubscriptionRenewed($subscription));
        }

        $this->info($renewableSubscriptions->count() . ' renewable subscriptions processed.');
    }
}

