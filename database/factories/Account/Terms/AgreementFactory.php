<?php

namespace Database\Factories\Account\Terms;

use App\Models\Account\Terms\Agreement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agreement>
 */
class AgreementFactory extends Factory
{
    protected $model = Agreement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'accountable_id' => User::factory(),
            'accountable_type' => (new User)->getMorphClass(),
            'terms_version_id' => config('terms.current_version'),
            'accepted_at' => now(),
            'declined_at' => null,
        ];
    }

    /**
     * Indicate the terms were declined.
     */
    public function declined(): static
    {
        return $this->state(fn (array $attributes): array => [
            'accepted_at' => null,
            'declined_at' => now(),
        ]);
    }

    /**
     * Indicate the terms are pending (neither accepted nor declined).
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'accepted_at' => null,
            'declined_at' => null,
        ]);
    }
}
