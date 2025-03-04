<?php

use LucaLongo\Subscriptions\Http\Controllers\Hooks\Payments\StripeController;

Route::post('/hooks/payments/stripe', StripeController::class);
