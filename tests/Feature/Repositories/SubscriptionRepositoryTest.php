<?php

use Illuminate\Support\Facades\Event;
use LucaLongo\Subscriptions\Contracts\PlanContract;
use LucaLongo\Subscriptions\Contracts\SubscriptionContract;
use LucaLongo\Subscriptions\Enums\Duration;
use LucaLongo\Subscriptions\Events\SubscriptionCancelled;
use LucaLongo\Subscriptions\Events\SubscriptionDowngraded;
use LucaLongo\Subscriptions\Events\SubscriptionReactivated;
use LucaLongo\Subscriptions\Events\SubscriptionUpgraded;
use LucaLongo\Subscriptions\Repositories\SubscriptionRepository;

beforeEach(function () {
    Event::fake();

    $this->subscriptionRepository = app(SubscriptionRepository::class);
    $this->subscriptionModel = app(SubscriptionContract::class);
    $this->planModel = app(PlanContract::class);

    $this->subscriber = \LucaLongo\Subscriptions\Tests\TestClasses\User::create([
        'name' => 'Test User',
        'password' => bcrypt('password'),
        'email' => 'test@example.com',
        'email_verified_at' => now(),
    ]);

    $this->basicPlan = $this->planModel::create([
        'code' => 'basic',
        'name' => 'Basic Plan',
        'pricing' => json_encode(['monthly' => ['worldwide' => 1000]]),
        'renewable' => true,
    ]);

    $this->premiumPlan = $this->planModel::create([
        'code' => 'premium',
        'name' => 'Premium Plan',
        'pricing' => json_encode(['monthly' => ['worldwide' => 2000]]),
        'renewable' => true,
    ]);

    $this->subscription = $this->subscriptionModel::create([
        'subscriber_id' => $this->subscriber->getKey(),
        'subscriber_type' => get_class($this->subscriber),
        'plan_id' => $this->basicPlan->getKey(),
        'billing_cycle' => Duration::MONTHLY,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'autorenew' => true,
        'price' => 1000,
    ]);
});

it('can cancel a subscription and emit the event', function () {
    $this->subscriptionRepository->cancel($this->subscription);

    expect($this->subscription->refresh()->ends_at)->toBeLessThan(now());

    Event::assertDispatched(SubscriptionCancelled::class);
});

it('can reactivate a cancelled subscription', function () {
    $this->subscription->update(['ends_at' => now()->subDay()]);

    $this->subscriptionRepository->reactivate($this->subscription);

    expect($this->subscription->refresh()->ends_at)->toBeGreaterThan(now());

    Event::assertDispatched(SubscriptionReactivated::class);
});

it('allows upgrade and emits event', function () {
    expect(
        $this->subscriptionRepository
            ->upgrade($this->subscription, $this->premiumPlan, Duration::MONTHLY)
    )->toBeTrue();

    expect($this->subscription->refresh()->plan_id)->toBe($this->premiumPlan->getKey());

    Event::assertDispatched(SubscriptionUpgraded::class);
});

it('denies upgrade if not allowed', function () {
    $repository = new class extends SubscriptionRepository {
        public function canUpgrade(SubscriptionContract $subscription, PlanContract $newPlan, Duration $newBillingCycle): bool
        {
            return false;
        }
    };

    $result = $repository->upgrade($this->subscription, $this->premiumPlan, Duration::MONTHLY);

    expect($result)->toBeFalse()
        ->and($this->subscription->refresh()->plan_id)->toBe($this->basicPlan->getKey());

    Event::assertNotDispatched(SubscriptionUpgraded::class);
});

it('allows downgrade and emits event', function () {
    $this->subscription->update(['plan_id' => $this->premiumPlan->getKey()]);

    $this->subscriptionRepository->downgrade($this->subscription, $this->basicPlan, Duration::MONTHLY);

    expect($this->subscription->refresh()->plan_id)->toBe($this->basicPlan->getKey());

    Event::assertDispatched(SubscriptionDowngraded::class);
});

it('denies downgrade if not allowed', function () {
    $repository = new class extends SubscriptionRepository {
        public function canDowngrade(SubscriptionContract $subscription, PlanContract $newPlan, Duration $newBillingCycle): bool
        {
            return false;
        }
    };

    $this->subscription->update(['plan_id' => $this->premiumPlan->getKey()]);

    $result = $repository->downgrade($this->subscription, $this->basicPlan, Duration::MONTHLY);

    expect($result)->toBeFalse()
        ->and($this->subscription->refresh()->plan_id)->toBe($this->premiumPlan->getKey());

    Event::assertNotDispatched(SubscriptionDowngraded::class);
});

it('renews a subscription correctly', function () {
    $this->subscription->update(['ends_at' => now()->subDay()]);

    $this->subscriptionRepository->renew($this->subscription);

    expect($this->subscription->refresh()->ends_at)->toBeGreaterThan(now());
});

it('supports changing billing cycle during renewal', function () {
    $this->subscription->update(['ends_at' => now()->subDay()]);

    $this->subscriptionRepository->renew($this->subscription, Duration::YEARLY);

    expect($this->subscription->refresh()->billing_cycle)->toBe(Duration::YEARLY)
        ->and($this->subscription->refresh()->price)->toBe($this->basicPlan->getPrice(Duration::YEARLY));
});
