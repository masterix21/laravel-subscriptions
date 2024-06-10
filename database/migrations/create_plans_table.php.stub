<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LucaLongo\Subscriptions\Enums\DurationInterval;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->boolean('enabled')->default(true);
            $table->boolean('hidden')->default(false);

            $table->nullableMorphs('subscribable');

            $table->string('code')->index();
            $table->string('name');

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            $table->unsignedInteger('duration_period')->unsigned()->nullable();
            $table->string('duration_interval')->nullable();

            $table->decimal('price')->unsigned()->default(0);

            $table->smallInteger('trial_period')->unsigned()->nullable();
            $table->string('trial_interval')->nullable();

            $table->smallInteger('invoice_period')->unsigned()->nullable();
            $table->string('invoice_interval')->nullable();

            $table->smallInteger('grace_period')->unsigned()->nullable();
            $table->string('grace_interval')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
