<?php

namespace LucaLongo\Subscriptions\Tests\Features\Actions\Subscriptions;

use LucaLongo\Subscriptions\Actions\Subscriptions\DisableAutoRenewSubscription;
use LucaLongo\Subscriptions\Enums\DurationInterval;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
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

    $this->subscription = $this->monthlyPlan->subscribe($this->user);
});

test('it disables auto_renew and returns true on successful save', function () {
    expect($this->subscription->auto_renew)->toBeTrue();

    expect(app(DisableAutoRenewSubscription::class)->execute($this->subscription))
        ->toBeTrue();

    expect($this->subscription->auto_renew)->toBeFalse();
});
