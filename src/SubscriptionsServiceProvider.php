<?php

namespace LucaLongo\Subscriptions;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SubscriptionsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-subscriptions')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                'create_plans_table',
                'create_features_table',
                'create_plan_feature_table',
            ]);
    }
}
