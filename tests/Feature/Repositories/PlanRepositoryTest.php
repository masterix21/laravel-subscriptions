<?php

use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Repositories\PlanRepository;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(fn () => $this->planRepository = new PlanRepository);

it('can create a plan', function () {
    $planData = [
        'code' => 'basic',
        'name' => 'Basic Plan',
        'pricing' => json_encode(['worldwide' => 10, 'US' => 8]),
        'features' => json_encode(['max_users' => 5]),
        'trial_days' => 7,
        'grace_days' => 3,
    ];

    $plan = $this->planRepository->create($planData);

    expect($plan)
        ->toBeInstanceOf(PlanContract::class)
        ->and($plan->code)
        ->toBe('basic');

    assertDatabaseHas('plans', ['code' => 'basic']);
});

it('can retrieve all plans', function () {
    $this->planRepository->create(['code' => 'basic', 'name' => 'Basic']);
    $this->planRepository->create(['code' => 'premium', 'name' => 'Premium']);

    $plans = $this->planRepository->all();

    expect($plans)->toHaveCount(2);
});

it('can find a plan by code', function () {
    $this->planRepository->create(['code' => 'basic', 'name' => 'Basic']);

    $plan = $this->planRepository->findByCode('basic');

    expect($plan)->not->toBeNull()
        ->and($plan->name)->toBe('Basic');
});

it('can delete a plan', function () {
    $plan = $this->planRepository->create(['code' => 'basic', 'name' => 'Basic']);

    $this->planRepository->delete($plan);

    assertDatabaseMissing('plans', ['code' => 'basic']);
});
