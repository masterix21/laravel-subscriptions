<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Events\SubscriptionRenewed;
use LucaLongo\Subscriptions\Tests\TestClasses\User;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    Event::fake();

    $this->subscriptionModel = app(SubscriptionContract::class);
    $this->planModel = app(PlanContract::class);

    $this->user = User::create([
        'name' => 'Test User',
        'password' => bcrypt('password'),
        'email' => 'test@example.com',
    ]);

    $this->renewablePlan = $this->planModel::create([
        'code' => 'premium',
        'name' => 'Premium Plan',
        'pricing' => json_encode(['worldwide' => 20]),
        'features' => json_encode(['max_users' => 10]),
        'trial_days' => 7,
        'grace_days' => 3,
        'renewable' => true,
        'duration' => 1,
    ]);

    $this->nonRenewablePlan = $this->planModel::create([
        'code' => 'basic',
        'name' => 'Basic Plan',
        'pricing' => json_encode(['worldwide' => 10]),
        'features' => json_encode(['max_users' => 5]),
        'trial_days' => 7,
        'grace_days' => 3,
        'renewable' => false,
        'duration' => 1,
    ]);
});

it('renews a renewable subscription with auto-renew enabled', function () {
    $subscription = $this->subscriptionModel::create([
        'subscriber_id' => $this->user->id,
        'subscriber_type' => get_class($this->user),
        'plan_id' => $this->renewablePlan->id,
        'ends_at' => now()->subMinute(),
        'autorenew' => true,
    ]);

    Artisan::call('subscriptions:check-renewal');

    Event::assertDispatched(SubscriptionRenewed::class);

    $subscription->refresh();

    expect($subscription->ends_at)
        ->toBeGreaterThan(now())
        ->and($subscription->autorenew)
        ->toBeTrue();

    assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'autorenew' => true,
    ]);
});

it('does not renew a subscription if auto-renew is disabled', function () {
    $subscription = $this->subscriptionModel::create([
        'subscriber_id' => $this->user->id,
        'subscriber_type' => get_class($this->user),
        'plan_id' => $this->renewablePlan->id,
        'ends_at' => now()->subMinute(),
        'autorenew' => false,
    ]);

    Artisan::call('subscriptions:check-renewal');

    Event::assertNotDispatched(SubscriptionRenewed::class);

    $subscription->refresh();

    expect($subscription->ends_at)->toBeLessThanOrEqual(now());

    assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'autorenew' => false,
    ]);
});

it('does not renew a non-renewable subscription even if auto-renew is enabled', function () {
    $subscription = $this->subscriptionModel::create([
        'subscriber_id' => $this->user->id,
        'subscriber_type' => get_class($this->user),
        'plan_id' => $this->nonRenewablePlan->id,
        'ends_at' => now()->subMinute(),
        'autorenew' => true,
    ]);

    Artisan::call('subscriptions:check-renewal');

    Event::assertNotDispatched(SubscriptionRenewed::class);

    $subscription->refresh();

    expect($subscription->autorenew)->toBeFalse();

    expect($subscription->ends_at)->toBeLessThanOrEqual(now());

    assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'autorenew' => false,
    ]);
});

it('disables auto-renew if the subscription is not renewable anymore', function () {
    $subscription = $this->subscriptionModel::create([
        'subscriber_id' => $this->user->id,
        'subscriber_type' => get_class($this->user),
        'plan_id' => $this->nonRenewablePlan->id,
        'ends_at' => now()->subMinute(),
        'autorenew' => true,
    ]);

    Artisan::call('subscriptions:check-renewal');

    $subscription->refresh();

    expect($subscription->autorenew)->toBeFalse();

    assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'autorenew' => false,
    ]);
});
