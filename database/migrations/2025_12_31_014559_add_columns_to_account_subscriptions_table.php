<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account_subscriptions', function (Blueprint $table): void {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_subscriptions', function (Blueprint $table): void {
            $table->dropMorphs('accountable');
            $table->dropForeign(['plan_id']);
            $table->dropColumn([
                'plan_id',
                'trial_ends_at',
                'starts_at',
                'ends_at',
                'canceled_at',
                'canceled_by',
                'cancellation_reason',
                'status',
            ]);
        });
    }
};
