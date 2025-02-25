<?php

namespace LucaLongo\Subscriptions;

use LucaLongo\Subscriptions\Console\CheckSubscriptionsRenewal;
use LucaLongo\Subscriptions\Console\CheckSubscriptionsStatus;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Repositories\Contracts\PlanRepositoryInterface;
use LucaLongo\Subscriptions\Repositories\Contracts\SubscriptionRepositoryInterface;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SubscriptionsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-subscriptions')
            ->hasConfigFile('subscriptions')
            ->hasViews('subscriptions')
            ->hasTranslations()
            ->hasCommands([
                CheckSubscriptionsStatus::class,
                CheckSubscriptionsRenewal::class,
            ])
            ->hasMigrations([
                'create_plans_tables',
                'create_subscriptions_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->bind(PlanContract::class, config('subscriptions.models.plan'));
        $this->app->bind(SubscriptionContract::class, config('subscriptions.models.subscription'));

        $this->app->bind(PlanRepositoryInterface::class, config('subscriptions.repositories.plan'));
        $this->app->bind(SubscriptionRepositoryInterface::class, config('subscriptions.repositories.subscription'));
    }

    public function packageBooted(): void {}
}
