<?php

namespace LucaLongo\Subscriptions;

use Livewire\Livewire;
use LucaLongo\Subscriptions\Livewire\Manage\Features;
use LucaLongo\Subscriptions\Livewire\Manage\Plans;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Payments\Contracts\GatewayContract;
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
            ])
            ->hasRoute('web');
    }

    public function packageBooted(): void
    {
        $this->app->bind(SubscriberContract::class, fn () => app(config('subscriptions.subscriber')));

        $this->app->bind(PlanContract::class, fn () => app(config('subscriptions.models.plan')));
        $this->app->bind(SubscriptionContract::class, fn () => app(config('subscriptions.models.subscription')));

        $this->app->bind(GatewayContract::class, fn () => app(config('subscriptions.payment_gateway')));
        $this->app->alias(GatewayContract::class, 'paymentGateway');

        $this->registerComponents();
    }

    protected function registerComponents(): self
    {
        if (class_exists('Livewire\\Livewire')) {
            Livewire::component('subscriptions::manage-features', Features::class);
            Livewire::component('subscriptions::manage-plans', Plans::class);
            Livewire::component('subscriptions::manage-subscriptions', \LucaLongo\Subscriptions\Livewire\Manage\Subscriptions::class);
        }

        return $this;
    }
}
