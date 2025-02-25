<?php

namespace LucaLongo\Subscriptions\Console;

use Illuminate\Console\Command;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Events\TrialExpired;
use LucaLongo\Subscriptions\Events\SubscriptionExpired;

class CheckSubscriptionsStatus extends Command
{
    protected $signature = 'subscriptions:check-status';
    protected $description = 'Check the status of subscriptions expiring within the current minute and trigger events if necessary.';

    public function handle(): void
    {
        $subscriptions = app(SubscriptionContract::class)::query()
            ->where(fn ($query) => $query->whereBetween('trial_ends_at', [
                now()->startOfMinute(),
                now()->endOfMinute(),
            ]))
            ->orWhere(fn ($query) => $query->whereBetween('grace_ends_at', [
                now()->startOfMinute(),
                now()->endOfMinute(),
            ]))
            ->get();

        foreach ($subscriptions as $subscription) {
            if ($subscription->trial_ends_at?->between(now()->startOfMinute(), now()->endOfMinute())) {
                event(new TrialExpired($subscription));
            }

            if ($subscription->grace_ends_at?->between(now()->startOfMinute(), now()->endOfMinute())) {
                event(new SubscriptionExpired($subscription));
            }
        }

        $this->info($subscriptions->count() .' subscription status updated.');
    }
}
