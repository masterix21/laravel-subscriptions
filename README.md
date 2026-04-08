# Laravel Subscriptions

[![Latest Version on Packagist](https://img.shields.io/packagist/v/masterix21/laravel-subscriptions.svg?style=flat-square)](https://packagist.org/packages/masterix21/laravel-subscriptions)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/masterix21/laravel-subscriptions/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/masterix21/laravel-subscriptions/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/masterix21/laravel-subscriptions/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/masterix21/laravel-subscriptions/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/masterix21/laravel-subscriptions.svg?style=flat-square)](https://packagist.org/packages/masterix21/laravel-subscriptions)

A flexible, payment-agnostic subscription system for Laravel. Manage plans, features, trials, grace periods, and recurring billing with built-in Stripe support and Livewire admin views.

## Requirements

- PHP 8.2+
- Laravel 12 or 13

## Installation

```bash
composer require masterix21/laravel-subscriptions
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="laravel-subscriptions-migrations"
php artisan migrate
```

Publish the config file:

```bash
php artisan vendor:publish --tag="laravel-subscriptions-config"
```

## Setup

### Prepare your Subscriber model

Your subscriber model (typically `User`) must implement `SubscriberContract` and use the `HasSubscriptions` trait.

The model also needs a `meta` JSON field for storing payment gateway data:

```php
// Migration
$table->json('meta')->nullable();
```

```php
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use LucaLongo\Subscriptions\Models\Concerns\HasSubscriptions;
use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;

class User extends Authenticatable implements SubscriberContract
{
    use HasSubscriptions;

    protected $fillable = [
        // ...
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => AsArrayObject::class,
        ];
    }

    public function customerName(): string
    {
        return $this->name;
    }

    public function customerEmail(): string
    {
        return $this->email;
    }

    public function customerUniqueIdentifierKey(): string
    {
        return $this->getKeyName();
    }

    public function customerUniqueIdentifier(): string
    {
        return (string) $this->getKey();
    }
}
```

### Configuration

The config file (`config/subscriptions.php`) allows you to customize the subscriber model, payment gateway, and all model classes:

```php
return [
    'subscriber' => \App\Models\User::class,
    'payment_gateway' => \LucaLongo\Subscriptions\Payments\Gateways\StripeGateway::class,

    'models' => [
        'plan' => \LucaLongo\Subscriptions\Models\Plan::class,
        'feature' => \LucaLongo\Subscriptions\Models\Feature::class,
        'plan_feature' => \LucaLongo\Subscriptions\Models\PlanFeature::class,
        'subscription' => \LucaLongo\Subscriptions\Models\Subscription::class,
    ],
];
```

## Usage

### Creating Plans

```php
use LucaLongo\Subscriptions\Models\Plan;
use LucaLongo\Subscriptions\Enums\DurationInterval;

$plan = Plan::create([
    'name' => 'Pro Monthly',
    'description' => 'Pro plan, billed monthly',
    'duration_period' => 1,
    'duration_interval' => DurationInterval::MONTH,
    'price' => 9.99,
    'trial_period' => 14,
    'trial_interval' => DurationInterval::DAY,
    'grace_period' => 3,
    'grace_interval' => DurationInterval::DAY,
]);
```

The `code` field is auto-generated from the `name` (e.g. `pro-monthly`).

#### Plan scopes

```php
Plan::active()->get();    // enabled = true
Plan::inactive()->get();  // enabled = false
Plan::visible()->get();   // hidden = false
Plan::invisible()->get(); // hidden = true
```

### Managing Features

```php
use LucaLongo\Subscriptions\Models\Feature;

$feature = Feature::create(['name' => 'API Access']);

// Attach features to a plan (with optional max usage)
$plan->features()->attach($feature, ['max_usage' => 1000]);
```

### Subscribing

```php
// From the plan
$subscription = $plan->subscribe($user);

// From the user
$subscription = $user->subscribe($plan);

// With options
$subscription = $plan->subscribe(
    subscriber: $user,
    status: SubscriptionStatus::TRIALING,
    autoRenew: false,
    data: [
        'next_billing_at' => now()->addMonths(2),
        'payment_provider' => 'stripe',
        'payment_provider_reference' => 'sub_xxx',
    ]
);
```

### Checking Subscription State

```php
$subscription->isActive();    // Currently active (or in grace period)
$subscription->onTrial();     // In trial period
$subscription->onGrace();     // In grace period
$subscription->isRevoked();   // Permanently revoked
$subscription->isRevokable(); // Can be revoked
```

### Checking Features

```php
// On the subscriber
$user->hasActiveFeature('api-access');
$user->hasAnyActiveFeatures(['api-access', 'export']);
$user->hasAllActiveFeatures(['api-access', 'export']);
$user->subscribedTo('pro-monthly'); // by code
$user->subscribedTo($plan);        // by model

// On the subscription
$subscription->hasFeature('api-access');
$subscription->hasAnyFeature(collect(['api-access', 'export']));
$subscription->hasAllFeature(collect(['api-access', 'export']));
```

### Managing Subscriptions

```php
// Cancel (ends at next billing date by default)
$subscription->cancel();
$subscription->cancel(now()->addDays(7)); // custom end date

// Renew
$subscription->renew();
$subscription->renew(now()->addMonths(3)); // custom next billing date

// Revoke (permanent, cannot be renewed)
$subscription->revoke();

// Auto-renewal
app(DisableAutoRenewSubscription::class)->execute($subscription);
app(EnableAutoRenewSubscription::class)->execute($subscription);
```

## Middleware

Three middleware are available to protect routes based on subscription features:

```php
use LucaLongo\Subscriptions\Http\Middleware\RequiresFeatureMiddleware;
use LucaLongo\Subscriptions\Http\Middleware\RequiresAnyFeaturesMiddleware;
use LucaLongo\Subscriptions\Http\Middleware\RequiresAllFeaturesMiddleware;

// Single feature
Route::middleware(RequiresFeatureMiddleware::class . ':api-access')
    ->get('/api/data', DataController::class);

// Any of multiple features (comma, pipe, or space separated)
Route::middleware(RequiresAnyFeaturesMiddleware::class . ':export,api-access')
    ->get('/tools', ToolsController::class);

// All features required
Route::middleware(RequiresAllFeaturesMiddleware::class . ':export,api-access')
    ->get('/advanced', AdvancedController::class);
```

All middleware return a `403` response if the user doesn't meet the requirements.

## Stripe Integration

### Setup

Add your Stripe credentials to `config/services.php`:

```php
'stripe' => [
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],
```

Store the Stripe Price ID in the plan's `meta` field:

```php
$plan->meta['stripe_id'] = 'price_xxx';
$plan->save();
```

### Creating a Checkout Session

```php
use LucaLongo\Subscriptions\Payments\Gateways\StripeGateway;

return app(StripeGateway::class)->subscribe(
    plan: $plan,
    subscriber: $user,
    successUrl: route('subscription.success'),
    cancelUrl: route('subscription.cancel'),
);
```

This redirects the user to Stripe Checkout. The webhook handler automatically syncs subscription state back to your database.

### Webhook

The package registers a webhook route at `POST /hooks/payments/stripe`. The handler processes these Stripe events:

- `customer.subscription.created`
- `customer.subscription.updated`
- `customer.subscription.deleted`
- `customer.deleted`

## Livewire Admin Views

The package includes optional Livewire components for managing plans, features, and subscriptions using Filament Table Builder.

Install the required dependencies:

```bash
composer require livewire/volt filament/tables guava/filament-clusters
```

Publish the views:

```bash
php artisan vendor:publish --tag="laravel-subscriptions-views"
```

Available components:

```blade
<livewire:subscriptions::manage-plans />
<livewire:subscriptions::manage-features />
<livewire:subscriptions::manage-subscriptions :subscriber="$user" />
```

## Extending Models

You can extend any model by creating your own class and updating the config:

```php
// app/Models/Plan.php
class Plan extends \LucaLongo\Subscriptions\Models\Plan
{
    // Your customizations
}

// config/subscriptions.php
'models' => [
    'plan' => \App\Models\Plan::class,
    // ...
],
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Luca Longo](https://github.com/masterix21)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
