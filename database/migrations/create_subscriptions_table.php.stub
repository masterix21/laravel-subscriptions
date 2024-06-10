<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->uuid()->index();

            $table
                ->string('payment_provider')
                ->nullable();

            $table
                ->string('payment_provider_reference')
                ->nullable()
                ->index();

            $table->morphs('subscriber');

            $table->foreignIdFor(config('subscriptions.models.plan'))
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('next_billing_at')->nullable();

            $table->decimal('price')->nullable();

            $table->dateTime('trial_starts_at')->nullable();
            $table->dateTime('trial_ends_at')->nullable();

            $table->dateTime('grace_starts_at')->nullable();
            $table->dateTime('grace_ends_at')->nullable();

            $table->dateTime('revoked_at')->nullable();

            $table->string('note')->nullable();

            $table->json('meta')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
