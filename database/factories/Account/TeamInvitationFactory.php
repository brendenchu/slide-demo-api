<?php

namespace Database\Factories\Account;

use App\Enums\Account\InvitationStatus;
use App\Enums\Account\TeamRole;
use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TeamInvitation>
 */
class TeamInvitationFactory extends Factory
{
    /**
     * @var class-string<TeamInvitation>
     */
    protected $model = TeamInvitation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'invited_by' => User::factory(),
            'email' => fake()->unique()->safeEmail(),
            'token' => Str::random(64),
            'role' => TeamRole::Member->value,
            'status' => InvitationStatus::Pending,
            'expires_at' => now()->addDays(7),
        ];
    }

    /**
     * Mark the invitation as accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (): array => [
            'status' => InvitationStatus::Accepted,
            'accepted_at' => now(),
        ]);
    }

    /**
     * Mark the invitation as expired.
     */
    public function expired(): static
    {
        return $this->state(fn (): array => [
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Set the invitation role to admin.
     */
    public function asAdmin(): static
    {
        return $this->state(fn (): array => [
            'role' => TeamRole::Admin->value,
        ]);
    }

    /**
     * Assign the invitation to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
