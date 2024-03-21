<?php

namespace LucaLongo\Subscriptions\Enums;

enum DurationInterval: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';

    public function label(): string
    {
        return match ($this) {
            self::DAY => __('days'),
            self::WEEK => __('weeks'),
            default => __('months'),
        };
    }

    public function labelSingular(): string
    {
        return match ($this) {
            self::DAY => __('day'),
            self::WEEK => __('week'),
            default => __('month'),
        };
    }
}
