<?php

namespace Database\Factories\Account;

use App\Models\Account\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Team>
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => $this->faker->unique()->regexify('[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}'),
            'key' => $this->faker->unique()->slug(),
            'label' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'status' => 1,
            'email' => $this->faker->email(),
            'phone' => $this->faker->phoneNumber(),
            'website' => $this->faker->url(),
        ];
    }

    /**
     * Set the team owner.
     */
    public function ownedBy(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'owner_id' => $user->id,
        ]);
    }
}
