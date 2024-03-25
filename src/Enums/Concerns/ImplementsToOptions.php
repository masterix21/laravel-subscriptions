<?php

namespace LucaLongo\Subscriptions\Enums\Concerns;

trait ImplementsToOptions
{
    public static function toOptions(): array
    {
        return collect(static::cases())
            ->map(function ($enum) {
                $key = $enum->value;
                $label = __($enum->value);

                if (method_exists($enum, 'label')) {
                    $label = $enum->label();
                }

                return compact('key', 'label');
            })
            ->pluck('label', 'key')
            ->toArray();
    }
}
