<?php

namespace LucaLongo\Subscriptions\Tests\Features\Actions\Plans;

use Carbon\Carbon;
use LucaLongo\Subscriptions\Actions\Subscriptions\DisableAutoRenewSubscription;
use LucaLongo\Subscriptions\Actions\Subscriptions\EnableAutoRenewSubscription;
use LucaLongo\Subscriptions\Enums\DurationInterval;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Subscription;
use LucaLongo\Subscriptions\Tests\TestClasses\User;

beforeEach(function () {
    $this->monthlyPlan = app(PlanContract::class)::create([
        'code' => 'monthly',
        'name' => 'Monthly Plan',
        'duration_period' => 1,
        'duration_interval' => DurationInterval::MONTH,
        'trial_period' => 7,
        'trial_interval' => DurationInterval::DAY,
        'grace_period' => 3,
        'grace_interval' => DurationInterval::DAY,
    ]);

    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
});

test('it subscribes a plan with active status and auto-renew', function () {
    $subscription = $this->monthlyPlan->subscribe($this->user);

    expect($subscription::class)
        ->toBe(Subscription::class)
        ->and($subscription->auto_renew)
        ->toBeTrue()
        ->and($subscription->status)
        ->toBe(SubscriptionStatus::ACTIVE)
        ->and($subscription->trial_ends_at)
        ->toBeInstanceOf(Carbon::class)
        ->and($subscription->grace_ends_at)
        ->toBeInstanceOf(Carbon::class);
});

test('it subscribes a plan and disable auto-renew', function () {
    $subscription = $this->monthlyPlan->subscribe($this->user);

    app(DisableAutoRenewSubscription::class)->execute($subscription);

    expect($subscription->auto_renew)->toBeFalse();
});

test('it subscribes a plan and enable auto-renew', function () {
    $subscription = $this->monthlyPlan->subscribe($this->user, autoRenew: false);

    expect($subscription->auto_renew)->toBeFalse();

    app(EnableAutoRenewSubscription::class)->execute($subscription);

    expect($subscription->auto_renew)->toBeTrue();
});

test('it does not overwrite an existing subscription when payment provider data is missing', function () {
    $planA = $this->monthlyPlan;

    $userA = User::create([
        'name' => 'User A',
        'email' => 'a@example.com',
        'password' => bcrypt('password'),
    ]);

    $existing = $planA->subscribe($userA);

    $planB = app(PlanContract::class)::create([
        'code' => 'monthly-b',
        'name' => 'Monthly Plan B',
        'duration_period' => 1,
        'duration_interval' => DurationInterval::MONTH,
    ]);

    $userB = User::create([
        'name' => 'User B',
        'email' => 'b@example.com',
        'password' => bcrypt('password'),
    ]);

    $newSubscription = $planB->subscribe($userB, data: [
        'next_billing_at' => now()->addDay(),
    ]);

    expect(Subscription::count())->toBe(2);

    $existing->refresh();

    expect($existing->subscriber_id)->toBe($userA->getKey())
        ->and($existing->plan_id)->toBe($planA->getKey())
        ->and($newSubscription->subscriber_id)->toBe($userB->getKey())
        ->and($newSubscription->plan_id)->toBe($planB->getKey())
        ->and($userB->subscriptions()->count())->toBe(1);
});

test('it updates the existing subscription when payment provider data matches', function () {
    $first = $this->monthlyPlan->subscribe($this->user, data: [
        'payment_provider' => 'stripe',
        'payment_provider_reference' => 'sub_123',
    ]);

    $second = $this->monthlyPlan->subscribe($this->user, data: [
        'payment_provider' => 'stripe',
        'payment_provider_reference' => 'sub_123',
    ]);

    expect(Subscription::count())->toBe(1)
        ->and($second->getKey())->toBe($first->getKey());
});

test('it calculates proper trial and grace period based on plan configuration', function () {
    $subscription = $this->monthlyPlan->subscribe($this->user);

    $expectedTrialEnd = now()->addDays(7);
    $expectedGraceEnd = now()->addMonth()->addDays(3);

    expect($subscription)
        ->toBeInstanceOf(Subscription::class)
        ->and($subscription->trial_ends_at->format('U'))
        ->toBe($expectedTrialEnd->format('U'))
        ->and($subscription->grace_ends_at->format('U'))
        ->toBe($expectedGraceEnd->format('U'));
});
