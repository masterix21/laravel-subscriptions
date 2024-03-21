<?php

namespace LucaLongo\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'meta'    => AsArrayObject::class,
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(config('subscriptions.models.subscription'));
    }
}
