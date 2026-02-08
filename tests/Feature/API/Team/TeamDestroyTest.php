<?php

use App\Enums\Account\TeamStatus;
use App\Models\Account\Team;
use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('allows team admin to delete a team', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['is_admin' => true]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}");

    $response->assertSuccessful();
    expect($team->fresh()->status->value)->toBe(TeamStatus::DELETED->value);
});

it('denies non-admin members from deleting a team', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['is_admin' => false]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}");

    $response->assertForbidden();
});

it('denies unauthenticated users from deleting a team', function (): void {
    $team = Team::factory()->create();

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}");

    $response->assertUnauthorized();
});

it('prevents deletion of the users current active team', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['is_admin' => true]);

    $user->setSetting('current_team', $team->key);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}");

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'You cannot delete your current active team. Switch to another team first.');
    expect($team->fresh()->status->value)->not->toBe(TeamStatus::DELETED->value);
});

it('prevents deletion of a personal team', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['is_personal' => true]);
    $team->users()->attach($user->id, ['is_admin' => true]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}");

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'Your default team cannot be deleted.');
    expect($team->fresh()->status->value)->not->toBe(TeamStatus::DELETED->value);
});

it('allows deletion of a team that is not current or personal', function (): void {
    $user = User::factory()->create();
    $currentTeam = Team::factory()->create();
    $otherTeam = Team::factory()->create();
    $currentTeam->users()->attach($user->id, ['is_admin' => true]);
    $otherTeam->users()->attach($user->id, ['is_admin' => true]);

    $user->setSetting('current_team', $currentTeam->key);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/teams/{$otherTeam->public_id}");

    $response->assertSuccessful();
    expect($otherTeam->fresh()->status->value)->toBe(TeamStatus::DELETED->value);
});

it('deletes associated projects when a team is deleted', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['is_admin' => true]);

    $project1 = Project::factory()->create(['user_id' => $user->id]);
    $project1->teams()->sync([$team->id]);

    $project2 = Project::factory()->create(['user_id' => $user->id]);
    $project2->teams()->sync([$team->id]);

    // Unrelated project should not be deleted
    $unrelatedProject = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}");

    $response->assertSuccessful();
    expect(Project::find($project1->id))->toBeNull();
    expect(Project::find($project2->id))->toBeNull();
    expect(Project::find($unrelatedProject->id))->not->toBeNull();
});
