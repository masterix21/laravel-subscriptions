<?php

namespace LucaLongo\Subscriptions\Enums;

use LucaLongo\Subscriptions\Enums\Concerns\ImplementsToOptions;

enum DurationInterval: string
{
    use ImplementsToOptions;

    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';

    public function label(): string
    {
        return match ($this) {
            self::DAY => __('days'),
            self::WEEK => __('weeks'),
            self::YEAR => __('years'),
            default => __('months'),
        };
    }

    public function labelSingular(): string
    {
        return match ($this) {
            self::DAY => __('day'),
            self::WEEK => __('week'),
            self::YEAR => __('year'),
            default => __('month'),
        };
    }
}
