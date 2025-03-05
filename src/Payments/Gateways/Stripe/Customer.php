<?php

namespace LucaLongo\Subscriptions\Payments\Gateways\Stripe;

use LucaLongo\Subscriptions\Models\Contracts\SubscriberContract;
use LucaLongo\Subscriptions\Payments\Contracts\CustomerContract;
use LucaLongo\Subscriptions\Payments\Gateways\StripeGateway;

class Customer implements CustomerContract
{
    public function __construct(protected StripeGateway $gateway)
    {
        // ...
    }

    /**
     * @return \Stripe\Customer
     */
    public function customerFindOrNew(SubscriberContract $subscriber): mixed
    {
        return $this->findByCustomerId($subscriber)
            ?: $this->findByUniqueIdentifier($subscriber)
            ?: $this->create($subscriber);
    }

    protected function evaluateCustomer(?\Stripe\Customer $customer): ?\Stripe\Customer
    {
        if (! $customer?->isDeleted()) {
            return $customer;
        }

        return null;
    }

    protected function findByCustomerId(SubscriberContract $subscriber): ?\Stripe\Customer
    {
        $customerId = $subscriber->meta['stripe_id'] ?? null;

        if (blank($customerId)) {
            return null;
        }

        return $this->evaluateCustomer(
            rescue(fn () => $this->gateway->client()->customers->retrieve($customerId), report: false)
        );
    }

    protected function findByUniqueIdentifier(SubscriberContract $subscriber): ?\Stripe\Customer
    {
        return $this->evaluateCustomer(
            rescue(fn () => $this->gateway->client()->customers->search([
                'query' => $subscriber->customerUniqueIdentifierKey().":'".$subscriber->customerUniqueIdentifier()."'",
            ])->first(), report: false)
        );
    }

    protected function create(SubscriberContract $subscriber): \Stripe\Customer
    {
        /** @var \Stripe\Customer $customer */
        $customer = $this->gateway->client()->customers->create([
            'name' => $subscriber->customerName(),
            'email' => $subscriber->customerEmail(),
            'metadata' => [
                $subscriber->customerUniqueIdentifierKey() => $subscriber->customerUniqueIdentifier(),
            ],
        ]);

        $subscriber->meta ??= [];
        $subscriber->meta['stripe_id'] = $customer->id;
        $subscriber->save();

        return $customer;
    }

    public function findSubscriberByCustomer(\Stripe\Customer|string $customer): SubscriberContract
    {
        if ($customer instanceof \Stripe\Customer) {
            $customer = $customer->id;
        }

        return app(SubscriberContract::class)->where('meta->stripe_id', $customer)->firstOrFail();
    }
}
