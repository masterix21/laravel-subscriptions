<?php

namespace LucaLongo\Subscriptions\Console;

use Illuminate\Console\Command;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Enums\Duration;
use LucaLongo\Subscriptions\Events\SubscriptionDowngraded;
use LucaLongo\Subscriptions\Events\SubscriptionRenewed;
use LucaLongo\Subscriptions\Repositories\PlanRepository;

class CheckSubscriptionsRenewal extends Command
{
    protected $signature = 'subscriptions:check-renewal';

    protected $description = 'Automatically renew renewable subscriptions with auto-renew enabled and apply pending downgrades.';

    public function handle(): void
    {
        $subscriptions = app(SubscriptionContract::class)::query()
            ->with(['subscriber'])
            ->where('ends_at', '<=', now())
            ->where('autorenew', true)
            ->get();

        /** @var SubscriptionContract $subscription */
        foreach ($subscriptions as $subscription) {
            if ($subscription->pending_downgrade) {
                $this->applyPendingDowngrade($subscription);
                continue;
            }

            if (! $subscription->isRenewable()) {
                $subscription->disableAutoRenew();
                continue;
            }

            if (! $subscription->renew()) {
                continue;
            }

            event(new SubscriptionRenewed($subscription));
        }

        $this->info($subscriptions->count() . ' subscriptions processed.');
    }

    protected function applyPendingDowngrade(SubscriptionContract $subscription): void
    {
        $newPlan = app(PlanRepository::class)::findById($subscription->pending_downgrade['plan_id']);

        if (! $newPlan) {
            $subscription->disableAutoRenew();
            return;
        }

        $newBillingCycle = Duration::from($subscription->pending_downgrade['billing_cycle']);
        $newPrice = $newPlan->getPrice($newBillingCycle, $subscription->subscriber->country ?? null);

        $subscription->update([
            'plan_id' => $newPlan->getKey(),
            'billing_cycle' => $newBillingCycle,
            'price' => $newPrice,
            'pending_downgrade' => null,
            'starts_at' => now(),
            'ends_at' => now()->addMonths($newBillingCycle->toMonths()),
        ]);

        $subscription->refresh();

        event(new SubscriptionDowngraded($subscription, $subscription->plan, $subscription));
    }
}
