<?php

namespace LucaLongo\Subscriptions\Tests\Features\Actions\Subscriptions;

use Carbon\Carbon;
use LucaLongo\Subscriptions\Actions\Subscriptions\CancelSubscription;
use LucaLongo\Subscriptions\Actions\Subscriptions\DisableAutoRenewSubscription;
use LucaLongo\Subscriptions\Actions\Subscriptions\EnableAutoRenewSubscription;
use LucaLongo\Subscriptions\Enums\DurationInterval;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;
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
        'grace_interval' => DurationInterval::DAY
    ]);

    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->subscription = $this->monthlyPlan->subscribe($this->user);
});

test('it cancels the subscription with default end date', function () {
    $expectedEndsAt = $this->subscription->next_billing_at->toImmutable();

    expect(
        app(CancelSubscription::class)->execute($this->subscription)
    )->toBeTrue();

    expect($this->subscription)
        ->toBeInstanceOf(SubscriptionContract::class)
        ->and($this->subscription->auto_renew)
        ->toBeFalse()
        ->and($this->subscription->status)
        ->toBe(SubscriptionStatus::CANCELED)
        ->and($this->subscription->canceled_at)
        ->toBeInstanceOf(Carbon::class)
        ->and($this->subscription->canceled_at->format('U'))
        ->toBe(now()->format('U'))
        ->and($this->subscription->ends_at->format('U'))
        ->toBe($expectedEndsAt->format('U'));
});

test('it cancels the subscription with a specified end date', function () {
    $expectedEndsAt = now();

    expect(
        app(CancelSubscription::class)->execute($this->subscription, endsAt: $expectedEndsAt)
    )->toBeTrue();

    expect($this->subscription)
        ->toBeInstanceOf(SubscriptionContract::class)
        ->and($this->subscription->auto_renew)
        ->toBeFalse()
        ->and($this->subscription->status)
        ->toBe(SubscriptionStatus::CANCELED)
        ->and($this->subscription->canceled_at)
        ->toBeInstanceOf(Carbon::class)
        ->and($this->subscription->canceled_at->format('U'))
        ->toBe(now()->format('U'))
        ->and($this->subscription->ends_at->format('U'))
        ->toBe($expectedEndsAt->format('U'));
});
