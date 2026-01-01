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
        Schema::rename('plans', 'account_plans');

        Schema::table('account_plans', function (Blueprint $table): void {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_plans', function (Blueprint $table): void {
            $table->dropColumn([
                'name',
                'slug',
                'description',
                'price',
                'interval',
                'trial_period',
                'trial_interval',
                'is_active',
                'features',
            ]);
        });

        Schema::rename('account_plans', 'plans');
    }
};
