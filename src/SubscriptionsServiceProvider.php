<?php

namespace LucaLongo\Subscriptions;

use Livewire\Livewire;
use LucaLongo\Subscriptions\Livewire\Manage\Features;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SubscriptionsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-subscriptions')
            ->hasConfigFile()
            ->hasViews('subscriptions')
            ->hasTranslations()
            ->hasMigrations([
                'create_plans_table',
                'create_features_table',
                'create_plan_feature_table',
                'create_subscriptions_table',
                'create_subscription_payments_table',
            ]);
    }

    public function packageBooted(): void
    {
        $this
            ->registerComponents();
    }

    protected function registerComponents(): self
    {
        if (class_exists('Livewire\\Livewire')) {
            Livewire::component('subscriptions::manage-features', Features::class);
        }

        return $this;
    }
}
