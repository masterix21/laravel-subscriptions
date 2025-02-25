<?php

namespace LucaLongo\Subscriptions\Enums;

enum PlanRelationType: string
{
    case UPGRADE = 'upgrade';
    case DOWNGRADE = 'downgrade';
}
