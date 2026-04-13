<?php

namespace LucaLongo\Subscriptions\Tests\Features\Models;

use LucaLongo\Subscriptions\Enums\DurationInterval;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Models\Subscription;
use LucaLongo\Subscriptions\Tests\TestClasses\User;

beforeEach(function () {
    $this->freezeTime();

    $this->plan = app(PlanContract::class)::create([
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

test('it returns a fresh subscription', function () {
    $subscription = $this->plan->subscribe($this->user);

    expect(Subscription::active()->pluck('id'))->toContain($subscription->id);
});

test('it returns a canceled subscription whose ends_at is still in the future', function () {
    $subscription = $this->plan->subscribe($this->user);
    $subscription->forceFill([
        'status' => SubscriptionStatus::CANCELED,
        'canceled_at' => now(),
        'ends_at' => now()->addDays(10),
    ])->save();

    expect(Subscription::active()->pluck('id'))->toContain($subscription->id);
});

test('it excludes a canceled subscription whose ends_at has passed', function () {
    $subscription = $this->plan->subscribe($this->user);
    $subscription->forceFill([
        'status' => SubscriptionStatus::CANCELED,
        'canceled_at' => now()->subDays(10),
        'ends_at' => now()->subDay(),
        'grace_ends_at' => now()->subDay(),
    ])->save();

    expect(Subscription::active()->pluck('id'))->not->toContain($subscription->id);
});

test('it excludes a subscription revoked in the past', function () {
    $subscription = $this->plan->subscribe($this->user);
    $subscription->forceFill([
        'revoked_at' => now()->subDay(),
    ])->save();

    expect(Subscription::active()->pluck('id'))->not->toContain($subscription->id);
});

test('it returns a subscription scheduled to be revoked in the future', function () {
    $subscription = $this->plan->subscribe($this->user);
    $subscription->forceFill([
        'revoked_at' => now()->addDays(5),
    ])->save();

    expect(Subscription::active()->pluck('id'))->toContain($subscription->id);
});

test('it returns a subscription whose ends_at has passed but is still within grace period', function () {
    $subscription = $this->plan->subscribe($this->user);
    $subscription->forceFill([
        'ends_at' => now()->subDay(),
        'grace_ends_at' => now()->addDays(2),
    ])->save();

    expect(Subscription::active()->pluck('id'))->toContain($subscription->id);
});

test('it excludes a subscription whose ends_at and grace period have both passed', function () {
    $subscription = $this->plan->subscribe($this->user);
    $subscription->forceFill([
        'ends_at' => now()->subDays(5),
        'grace_ends_at' => now()->subDay(),
    ])->save();

    expect(Subscription::active()->pluck('id'))->not->toContain($subscription->id);
});

test('it returns a subscription with no ends_at', function () {
    $subscription = $this->plan->subscribe($this->user);
    $subscription->forceFill([
        'ends_at' => null,
        'grace_ends_at' => null,
        'next_billing_at' => null,
    ])->save();

    expect(Subscription::active()->pluck('id'))->toContain($subscription->id);
});

test('activeSubscriptions relation returns canceled subscription still within ends_at', function () {
    $subscription = $this->plan->subscribe($this->user);
    $subscription->forceFill([
        'status' => SubscriptionStatus::CANCELED,
        'canceled_at' => now(),
        'ends_at' => now()->addDays(10),
    ])->save();

    expect($this->user->activeSubscriptions()->get())
        ->toHaveCount(1)
        ->and($this->user->activeSubscriptions()->first())
        ->toBeInstanceOf(SubscriptionContract::class)
        ->and($this->user->activeSubscriptions()->first()->id)
        ->toBe($subscription->id);
});
