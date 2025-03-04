<?php

namespace LucaLongo\Subscriptions\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use LucaLongo\Subscriptions\SubscriptionsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'LucaLongo\\Subscriptions\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            SubscriptionsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        (include __DIR__.'/database/migrations/2014_10_12_000000_create_users_table.php')->up();
        (include __DIR__.'/../database/migrations/create_plans_table.php.stub')->up();
        (include __DIR__.'/../database/migrations/create_features_table.php.stub')->up();
        (include __DIR__.'/../database/migrations/create_plan_feature_table.php.stub')->up();
        (include __DIR__.'/../database/migrations/create_subscriptions_table.php.stub')->up();
        (include __DIR__.'/../database/migrations/create_subscription_payments_table.php.stub')->up();
    }
}
