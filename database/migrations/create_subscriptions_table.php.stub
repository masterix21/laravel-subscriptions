<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LucaLongo\Subscriptions\Enums\SubscriptionStatus;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->string('status')->default(SubscriptionStatus::ACTIVE->value);
            $table->string('payment_provider')->nullable();
            $table->string('payment_provider_reference')->nullable()->index();

            $table->boolean('auto_renew')->default(true);

            $table->morphs('subscriber');

            $table->foreignIdFor(config('subscriptions.models.plan'))
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->dateTime('ends_at')->nullable();
            $table->dateTime('next_billing_at')->nullable();
            $table->decimal('price')->nullable();

            $table->dateTime('trial_ends_at')->nullable();
            $table->dateTime('grace_ends_at')->nullable();

            $table->dateTime('canceled_at')->nullable();
            $table->dateTime('revoked_at')->nullable();

            $table->string('note')->nullable();

            $table->json('meta')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
