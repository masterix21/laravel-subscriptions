<?php

use Illuminate\Support\Facades\Event;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Enums\Duration;
use LucaLongo\Subscriptions\Events\SubscriptionCreated;
use LucaLongo\Subscriptions\Exceptions\NotStackablePlanException;
use LucaLongo\Subscriptions\Exceptions\ReachedMaxStackedPlan;
use LucaLongo\Subscriptions\Repositories\PlanRepository;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    Event::fake();

    $this->planRepository = app(PlanRepository::class);
    $this->planModel = app(PlanContract::class);
    $this->subscriptionModel = app(SubscriptionContract::class);

    $this->subscriber = \LucaLongo\Subscriptions\Tests\TestClasses\User::create([
        'name' => 'Test User',
        'password' => bcrypt('password'),
        'email' => 'test@example.com',
        'email_verified_at' => now(),
    ]);

    $this->stackablePlan = $this->planModel::create([
        'code' => 'stackable_plan',
        'name' => 'Stackable Plan',
        'is_stackable' => true,
        'stackable_limit' => 3,
        'pricing' => ['monthly' => ['worldwide' => 1000]],
    ]);

    $this->nonStackablePlan = $this->planModel::create([
        'code' => 'non_stackable_plan',
        'name' => 'Non-Stackable Plan',
        'is_stackable' => false,
        'pricing' => ['monthly' => ['worldwide' => 1000]],
    ]);
});

it('allows subscribing to a stackable plan within the limit', function () {
    $subscription = $this->planRepository->subscribe($this->stackablePlan, $this->subscriber, [
        'billing_cycle' => Duration::MONTHLY->value,
    ]);

    expect($subscription)->toBeInstanceOf(SubscriptionContract::class);
    assertDatabaseHas('subscriptions', [
        'subscriber_id' => $this->subscriber->getKey(),
        'plan_id' => $this->stackablePlan->getKey(),
    ]);

    Event::assertDispatched(SubscriptionCreated::class);
});

it('throws an exception if trying to subscribe to a non-stackable plan twice', function () {
    $this->planRepository->subscribe($this->nonStackablePlan, $this->subscriber, [
        'billing_cycle' => Duration::MONTHLY->value,
    ]);

    $this->expectException(NotStackablePlanException::class);

    $this->planRepository->subscribe($this->nonStackablePlan, $this->subscriber, [
        'billing_cycle' => Duration::MONTHLY->value,
    ]);
});

it('throws an exception if exceeding the stackable plan limit', function () {
    for ($i = 0; $i < 3; $i++) {
        $this->planRepository->subscribe($this->stackablePlan, $this->subscriber, [
            'billing_cycle' => Duration::MONTHLY->value,
        ]);
    }

    $this->expectException(ReachedMaxStackedPlan::class);

    $this->planRepository->subscribe($this->stackablePlan, $this->subscriber, [
        'billing_cycle' => Duration::MONTHLY->value,
    ]);
});

it('assigns the correct billing cycle and price', function () {
    $subscription = $this->planRepository->subscribe($this->stackablePlan, $this->subscriber, [
        'billing_cycle' => Duration::YEARLY->value,
        'country' => 'IT',
    ]);

    expect($subscription->billing_cycle)->toBe(Duration::YEARLY);

    expect($subscription->price)
        ->toBe($this->stackablePlan->getPrice(Duration::YEARLY, 'IT'));
});
