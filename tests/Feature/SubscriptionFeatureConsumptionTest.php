<?php

use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->subscriptionModel = app(SubscriptionContract::class);
    $this->planModel = app(PlanContract::class);

    $this->plan = $this->planModel::create([
        'code' => 'premium',
        'name' => 'Premium Plan',
        'pricing' => [
            'monthly' => ['worldwide' => 1000],
        ],
        'features' => [
            'max_users' => 10,
            'max_vehicles' => 5,
        ],
    ]);

    $this->subscription = $this->subscriptionModel::create([
        'subscriber_id' => 1,
        'subscriber_type' => 'TestUser',
        'plan_id' => $this->plan->id,
        'billing_cycle' => 'monthly',
        'consumed_features' => [],
    ]);
});

it('can retrieve feature limits from the plan', function () {
    expect($this->plan->getFeature('max_users'))->toBe(10)
        ->and($this->plan->getFeature('max_vehicles'))->toBe(5)
        ->and($this->plan->getFeature('non_existent', 100))->toBe(100); // Default value
});

it('can retrieve consumed feature count', function () {
    expect($this->subscription->getConsumedFeature('max_users'))->toBe(0)
        ->and($this->subscription->getConsumedFeature('max_vehicles'))->toBe(0);
});

it('allows feature usage if within limits', function () {
    expect($this->subscription->canConsumeFeature('max_users'))->toBeTrue()
        ->and($this->subscription->canConsumeFeature('max_vehicles'))->toBeTrue();
});

it('denies feature usage if limit is reached', function () {
    $this->subscription->update([
        'consumed_features' => [
            'max_users' => 10,
            'max_vehicles' => 5,
        ],
    ]);

    expect($this->subscription->canConsumeFeature('max_users'))->toBeFalse()
        ->and($this->subscription->canConsumeFeature('max_vehicles'))->toBeFalse();
});

it('correctly consumes features', function () {
    $this->subscription->consumeFeature('max_users');
    $this->subscription->consumeFeature('max_vehicles', 2);

    expect($this->subscription->getConsumedFeature('max_users'))->toBe(1)
        ->and($this->subscription->getConsumedFeature('max_vehicles'))->toBe(2);

    assertDatabaseHas('subscriptions', [
        'id' => $this->subscription->id,
        'consumed_features' => json_encode([
            'max_users' => 1,
            'max_vehicles' => 2,
        ]),
    ]);
});

it('prevents consumption when feature limit is reached', function () {
    $this->subscription->update([
        'consumed_features' => ['max_users' => 10],
    ]);

    expect($this->subscription->consumeFeature('max_users'))->toBeFalse()
        ->and($this->subscription->getConsumedFeature('max_users'))->toBe(10);
});
