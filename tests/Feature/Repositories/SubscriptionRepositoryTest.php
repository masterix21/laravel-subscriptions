<?php

use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Events\SubscriptionCancelled;
use LucaLongo\Subscriptions\Events\SubscriptionCreated;
use LucaLongo\Subscriptions\Events\SubscriptionDowngraded;
use LucaLongo\Subscriptions\Events\SubscriptionUpgraded;
use LucaLongo\Subscriptions\Repositories\SubscriptionRepository;
use LucaLongo\Subscriptions\Tests\TestClasses\User;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    Event::fake();

    $this->subscriptionRepository = app(SubscriptionRepository::class);

    $this->user = User::create([
        'name' => 'Test User',
        'password' => bcrypt('password'),
        'email' => 'test@example.com',
    ]);

    $this->plan = app(PlanContract::class)::create([
        'code' => 'basic',
        'name' => 'Basic Plan',
        'pricing' => json_encode(['worldwide' => 10]),
        'features' => json_encode(['max_users' => 5]),
        'trial_days' => 7,
        'grace_days' => 3,
    ]);

    $this->newPlan = app(\LucaLongo\Subscriptions\Contracts\PlanContract::class)::create([
        'code' => 'premium',
        'name' => 'Premium Plan',
        'pricing' => json_encode(['worldwide' => 20]),
        'features' => json_encode(['max_users' => 10]),
        'trial_days' => 7,
        'grace_days' => 3,
    ]);
});

it('can subscribe a user to a plan and emit the event', function () {
    $subscription = $this->subscriptionRepository->subscribe($this->user, $this->plan, []);

    expect($subscription)->toBeInstanceOf(SubscriptionContract::class)
        ->and($subscription->plan_id)->toBe($this->plan->id);

    assertDatabaseHas('subscriptions', ['subscriber_id' => $this->user->id, 'plan_id' => $this->plan->id]);

    Event::assertDispatched(SubscriptionCreated::class);
});

it('can find an active subscription', function () {
    $this->subscriptionRepository->subscribe($this->user, $this->plan, []);

    $subscription = $this->subscriptionRepository->findActiveBySubscriber($this->user);

    expect($subscription)->not->toBeNull();
});

it('can cancel a subscription', function () {
    $subscription = $this->subscriptionRepository->subscribe($this->user, $this->plan, []);

    $this->subscriptionRepository->cancel($subscription);

    expect($subscription->refresh()->ends_at)->toBeLessThan(now());

    Event::assertDispatched(SubscriptionCancelled::class);
});

it('can upgrade a subscription and emit the event', function () {
    $subscription = $this->subscriptionRepository->subscribe($this->user, $this->plan, []);

    $this->subscriptionRepository->upgrade($subscription, $this->newPlan);

    Event::assertDispatched(SubscriptionUpgraded::class);
});

it('deny subscription upgrade', function () {
    $repository = new class extends SubscriptionRepository
    {
        public function canUpgrade(SubscriptionContract $subscription, PlanContract $newPlan): bool
        {
            return false;
        }
    };

    $subscription = $repository->subscribe($this->user, $this->plan, []);

    $result = $repository->upgrade($subscription, $this->newPlan);

    expect($result)->toBeFalse()
        ->and($subscription->refresh()->plan_id)->toBe($this->plan->id);

    Event::assertNotDispatched(SubscriptionUpgraded::class);
});

it('can downgrade a subscription and emit the event', function () {
    $subscription = $this->subscriptionRepository->subscribe($this->user, $this->plan, []);

    $this->subscriptionRepository->downgrade($subscription, $this->newPlan);

    Event::assertDispatched(SubscriptionDowngraded::class);
});

it('deny subscription downgrade', function () {
    $repository = new class extends SubscriptionRepository
    {
        public function canDowngrade(SubscriptionContract $subscription, PlanContract $newPlan): bool
        {
            return false;
        }
    };

    $subscription = $repository->subscribe($this->user, $this->plan, []);

    $result = $repository->downgrade($subscription, $this->newPlan);

    expect($result)->toBeFalse()
        ->and($subscription->refresh()->plan_id)->toBe($this->plan->id);

    Event::assertNotDispatched(SubscriptionDowngraded::class);
});
