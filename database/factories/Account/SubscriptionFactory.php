<?php

namespace Database\Factories\Account;

use App\Models\Account\Plan;
use App\Models\Account\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'accountable_type' => User::class,
            'accountable_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'trial_ends_at' => null,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'canceled_at' => null,
            'canceled_by' => null,
            'cancellation_reason' => null,
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the subscription is on trial.
     */
    public function onTrial(): static
    {
        return $this->state(fn (array $attributes): array => [
            'trial_ends_at' => now()->addDays(7),
        ]);
    }

    /**
     * Indicate that the subscription is canceled.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'canceled_at' => now(),
            'status' => 'canceled',
        ]);
    }

    /**
     * Indicate that the subscription is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the subscription is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'ends_at' => now()->subDay(),
        ]);
    }
}
