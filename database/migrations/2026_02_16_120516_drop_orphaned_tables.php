<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('account_subscriptions');
        Schema::dropIfExists('account_plans');
        Schema::dropIfExists('read_receipts');
        Schema::dropIfExists('account_violations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('account_violations', function ($table): void {
            $table->id();
            $table->morphs('accountable');
            $table->string('public_id')->unique();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('read_receipts', function ($table): void {
            $table->id();
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->string('public_id')->unique();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('account_plans', function ($table): void {
            $table->id();
            $table->softDeletes();
            $table->timestamps();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('interval');
            $table->integer('trial_period')->default(0);
            $table->string('trial_interval')->default('day');
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable();
        });

        Schema::create('account_subscriptions', function ($table): void {
            $table->id();
            $table->softDeletes();
            $table->timestamps();
            $table->morphs('accountable');
            $table->foreignId('plan_id')->constrained('account_plans')->onDelete('cascade');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('canceled_at')->nullable();
            $table->unsignedBigInteger('canceled_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('status')->default('active');
        });
    }
};
