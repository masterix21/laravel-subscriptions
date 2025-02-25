<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Events\SubscriptionExpired;
use LucaLongo\Subscriptions\Events\TrialExpired;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    Event::fake();

    $this->subscriptionModel = app(SubscriptionContract::class);
});

it('dispatches TrialExpired event when trial ends within the current minute', function () {
    $subscription = $this->subscriptionModel::create([
        'subscriber_id' => 1,
        'subscriber_type' => 'TestUser',
        'plan_id' => 1,
        'trial_ends_at' => now()->startOfMinute(),
        'ends_at' => now()->addMonth(),
    ]);

    Artisan::call('subscriptions:check-status');

    Event::assertDispatched(TrialExpired::class);
    Event::assertNotDispatched(SubscriptionExpired::class);

    assertDatabaseHas('subscriptions', ['id' => $subscription->id]);
});

it('dispatches SubscriptionExpired event when grace period ends within the current minute', function () {
    $subscription = $this->subscriptionModel::create([
        'subscriber_id' => 1,
        'subscriber_type' => 'TestUser',
        'plan_id' => 1,
        'ends_at' => now()->subMinutes(1),
        'grace_ends_at' => now()->startOfMinute(),
    ]);

    Artisan::call('subscriptions:check-status');

    Event::assertDispatched(SubscriptionExpired::class);
    Event::assertNotDispatched(TrialExpired::class);

    assertDatabaseHas('subscriptions', ['id' => $subscription->id]);
});

it('does not dispatch events for active subscriptions', function () {
    $this->subscriptionModel::create([
        'subscriber_id' => 1,
        'subscriber_type' => 'TestUser',
        'plan_id' => 1,
        'trial_ends_at' => now()->addDays(3),
        'ends_at' => now()->addMonth(),
    ]);

    Artisan::call('subscriptions:check-status');

    Event::assertNotDispatched(TrialExpired::class);
    Event::assertNotDispatched(SubscriptionExpired::class);
});
