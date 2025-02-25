<?php

namespace LucaLongo\Subscriptions\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LucaLongo\Subscriptions\SubscriptionsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'LucaLongo\\Subscriptions\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            SubscriptionsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        (include __DIR__.'/../database/migrations/create_plans_tables.php.stub')->up();
        (include __DIR__.'/../database/migrations/create_subscriptions_table.php.stub')->up();
        (include __DIR__.'/database/2014_10_12_000000_create_users_table.php')->up();
    }
}
