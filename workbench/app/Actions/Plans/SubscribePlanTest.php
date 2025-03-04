<?php

namespace Workbench\App\Actions\Plans;

use Carbon\Carbon;
use LucaLongo\Subscriptions\Actions\Plans\SubscribePlan;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;
use LucaLongo\Subscriptions\Models\Contracts\PlanContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Models\Contracts\SubscriptionContract;

test('it subscribes a plan with active status and auto-renew', function () {
    $plan = mock(PlanContract::class)->expect(
        hasTrial: true,
        hasGrace: true,
        duration_period: 1,
        duration_interval: 'month',
        trial_period: 7,
        trial_interval: 'days',
        grace_period: 3,
        grace_interval: 'days'
    );

    $subscriber = mock(SubscriberContract::class)->expect(
        subscriber: fn () => $this->expectNotToPerformAssertions()
    );

    $subscription = mock(SubscriptionContract::class)->expect(
        save: fn () => true,
        fill: fn () => $subscription
    );

    app()->instance(SubscriptionContract::class, $subscription);

    $action = new SubscribePlan;

    $result = $action->subscribe($plan, $subscriber);

    expect($result)
        ->toBe($subscription)
        ->and($result->auto_renew)
        ->toBeTrue()
        ->and($result->status)
        ->toBe(SubscriptionStatus::ACTIVE)
        ->and($result->trial_ends_at)
        ->toBeInstanceOf(Carbon::class)
        ->and($result->grace_ends_at)
        ->toBeInstanceOf(Carbon::class);
});

test('it subscribes a plan without auto-renew', function () {
    $plan = mock(PlanContract::class)->expect(
        hasTrial: false,
        hasGrace: false,
        duration_period: 1,
        duration_interval: 'month'
    );

    $subscriber = mock(SubscriberContract::class)->expect(
        subscriber: fn () => $this->expectNotToPerformAssertions()
    );

    $subscription = mock(SubscriptionContract::class)->expect(
        save: fn () => true,
        fill: fn () => $subscription
    );

    app()->instance(SubscriptionContract::class, $subscription);

    $action = new SubscribePlan;

    $result = $action->subscribe($plan, $subscriber, SubscriptionStatus::CANCELED, false);

    expect($result)
        ->toBe($subscription)
        ->and($result->auto_renew)
        ->toBeFalse()
        ->and($result->status)
        ->toBe(SubscriptionStatus::CANCELED)
        ->and($result->next_billing_at)
        ->toBeNull();
});

test('it calculates proper trial and grace period based on plan configuration', function () {
    $plan = mock(PlanContract::class)->expect(
        hasTrial: true,
        hasGrace: true,
        trial_period: 14,
        trial_interval: 'days',
        grace_period: 7,
        grace_interval: 'days',
        duration_period: 1,
        duration_interval: 'month'
    );

    $subscriber = mock(SubscriberContract::class)->expect(
        subscriber: fn () => $this->expectNotToPerformAssertions()
    );

    $subscription = mock(SubscriptionContract::class)->expect(
        save: fn () => true,
        fill: fn () => $subscription
    );

    app()->instance(SubscriptionContract::class, $subscription);

    $action = new SubscribePlan;

    $result = $action->subscribe($plan, $subscriber);

    $expectedTrialEnd = now()->addDays(14);
    $expectedGraceEnd = $expectedTrialEnd->copy()->addMonth()->addDays(7);

    expect($result)
        ->toBe($subscription)
        ->and($result->trial_ends_at->toDateString())
        ->toBe($expectedTrialEnd->toDateString())
        ->and($result->grace_ends_at->toDateString())
        ->toBe($expectedGraceEnd->toDateString());
});
