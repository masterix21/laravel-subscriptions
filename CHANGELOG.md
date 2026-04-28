# Changelog

All notable changes to `laravel-subscriptions` will be documented in this file.

## 1.2.4 - 2026-04-28

### Fixes
- Fix `SubscribePlan` action overwriting an arbitrary existing subscription when `payment_provider` and `payment_provider_reference` are not provided. The query is now constrained to the current subscriber and provider reference, otherwise a fresh model instance is created.

### Tests
- Add regression tests covering `SubscribePlan` with and without payment provider data.

### Dependencies
- Bump `dependabot/fetch-metadata` from 3.0.0 to 3.1.0 (#32).

**Full Changelog**: https://github.com/masterix21/laravel-subscriptions/compare/1.2.3...1.2.4

## 1.2.3 - 2026-04-13

### Fixes
- Fix `active` scope on `Subscription` to rely on dates instead of status, so canceled subscriptions still within `ends_at` are correctly returned. `revoked_at` is now also treated as a date boundary (active if null or in the future).

**Full Changelog**: https://github.com/masterix21/laravel-subscriptions/compare/1.2.2...1.2.3

## 1.2.2 - 2026-04-09

### Fixes
- Move `Tabs` and `Grid` imports to `Filament\Schemas\Components` for Filament v5
- Move table actions from `Filament\Tables\Actions` to `Filament\Actions` for Filament v5
- Add missing Italian translations for all Filament form and table labels

**Full Changelog**: https://github.com/masterix21/laravel-subscriptions/compare/1.2.1...1.2.2

## 1.2.1 - 2026-04-09

### Fixes
- Replace `Filament\Forms\Form` with `Filament\Schemas\Schema` for Filament v5 compatibility
- Fix orphan `invoice_period`/`invoice_interval` references to use `duration_period`/`duration_interval` in Plan model, PlanTable, and SubscriptionTable
- Add PHP 8.5 support
- Freeze time in CancelSubscriptionTest

**Full Changelog**: https://github.com/masterix21/laravel-subscriptions/compare/1.2.0...1.2.1

## 1.2.0 - 2026-04-08

### Features
- Add Laravel 13 support
- Add Pest 4 support

### Changes
- Replace `guava/filament-clusters` with native Filament `FusedGroup` (requires Filament 4+)
- Bump `filament/tables` to `^4.0|^5.0`
- Bump `stripe/stripe-php` to `^20.0`
- Rewrite README with comprehensive documentation

### Fixes
- Fix CI: remove coverage config from phpunit.xml.dist that caused failures without coverage driver
- Fix CI: set fail-fast to false, fix install step, remove incompatible `--ci` flag for Pest 4
- Fix PHPStan errors and regenerate baseline for updated dependencies
- Load LivewireServiceProvider conditionally in tests

**Full Changelog**: https://github.com/masterix21/laravel-subscriptions/compare/1.0.3...1.2.0

## 1.0.3 - 2025-03-05

- FIX: trial subscriptions are now active

**Full Changelog**: https://github.com/masterix21/laravel-subscriptions/compare/1.0.2...1.0.3

## 1.0.2 - 2025-03-05

- Add check to active subscription status

**Full Changelog**: https://github.com/masterix21/laravel-subscriptions/compare/1.0.1...1.0.2

## 1.0.1 - 2025-03-05

- Improve Stripe webhooks to keep in sync a subscription

**Full Changelog**: https://github.com/masterix21/laravel-subscriptions/compare/1.0.0...1.0.1

## 1.0.0 - 2025-03-04

### What's Changed

* Add support to Laravel 10
* Add support to payment gateways
* Implements Stripe gateway
* Add Stripe web-hooks
* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/masterix21/laravel-subscriptions/pull/5
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/masterix21/laravel-subscriptions/pull/6
* 1.0.0 by @masterix21 in https://github.com/masterix21/laravel-subscriptions/pull/7

### New Contributors

* @masterix21 made their first contribution in https://github.com/masterix21/laravel-subscriptions/pull/7

**Full Changelog**: https://github.com/masterix21/laravel-subscriptions/compare/0.0.9...1.0.0

## 0.0.9 - 2024-07-25

- [FIX] Subscriptions livewire component crashes when the invoice period is empty

## 0.0.8 - 2024-07-09

- Bug fixing

## 0.0.7 - 2024-07-04

- Add dateTime format to the Next Billing At column

## 0.0.6 - 2024-07-04

- Add the next billing column to the subscriptions table

## 0.0.5 - 2024-07-03

- Minor changes

## 0.0.4 - 2024-06-21

- CreateSubscription now resolves the right model

## 0.0.3 - 2024-06-21

- Improved Livewire component to manage a subscription

## 0.0.2 - 2024-06-10

- Add provider references to subscriptions

## 0.0.1 - 2024-04-16

Our start point (beta)
