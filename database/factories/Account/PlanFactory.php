<?php

namespace Database\Factories\Account;

use App\Models\Account\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' Plan',
            'slug' => fake()->slug(2),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 9.99, 99.99),
            'interval' => fake()->randomElement(['monthly', 'yearly']),
            'trial_period' => fake()->randomElement([0, 7, 14, 30]),
            'trial_interval' => 'day',
            'is_active' => true,
            'features' => ['feature1', 'feature2', 'feature3'],
        ];
    }

    /**
     * Indicate that the plan is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the plan has no trial.
     */
    public function noTrial(): static
    {
        return $this->state(fn (array $attributes): array => [
            'trial_period' => 0,
        ]);
    }
}
