<?php

namespace Database\Factories\Story;

use App\Enums\Story\ProjectStatus;
use App\Models\Account\Team;
use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'user_id' => User::factory(),
            'key' => fake()->slug(3),
            'label' => ucfirst($name),
            'description' => fake()->sentence(),
            'status' => ProjectStatus::Draft,
            'current_step' => 'intro',
            'responses' => [],
        ];
    }

    /**
     * Configure the model factory to attach to a team after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function ($project): void {
            if ($project->teams()->count() === 0) {
                $team = Team::factory()->create();
                $project->teams()->attach($team);
            }
        });
    }
}
