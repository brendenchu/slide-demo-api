<?php

use App\Enums\Account\TeamRole;
use App\Models\Account\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('updates a team by id when user is an admin', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create(['label' => 'Old Name']);
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'name' => 'New Name',
        'description' => 'Updated description',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'status',
                'created_at',
                'updated_at',
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'New Name',
            ],
            'message' => 'Team updated successfully',
        ]);

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'label' => 'New Name',
        'description' => 'Updated description',
    ]);
});

it('updates a team by public_id when user is an admin', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create(['label' => 'Old Name']);
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->public_id}", [
        'name' => 'New Name',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'New Name',
            ],
        ]);
});

it('updates only name when only name is provided', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create(['label' => 'Old Name', 'description' => 'Original description']);
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'name' => 'New Name',
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'label' => 'New Name',
        'description' => 'Original description',
    ]);
});

it('updates only description when only description is provided', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create(['label' => 'Team Name', 'description' => 'Old description']);
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'description' => 'New description',
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'label' => 'Team Name',
        'description' => 'New description',
    ]);
});

it('can clear description by setting it to null', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create(['description' => 'Some description']);
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'description' => null,
    ]);

    $response->assertSuccessful();

    $team->refresh();
    expect($team->description)->toBeNull();
});

it('updates team status to active', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create(['status' => 2]);
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'status' => 'active',
    ]);

    $response->assertSuccessful();

    $team->refresh();
    expect($team->status->key())->toBe('active');
});

it('updates team status to inactive', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create(['status' => 1]);
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'status' => 'inactive',
    ]);

    $response->assertSuccessful();

    $team->refresh();
    expect($team->status->key())->toBe('inactive');
});

it('validates name is string', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'name' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('validates name max length', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'name' => str_repeat('a', 256),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('validates description is string', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'description' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['description']);
});

it('validates status is valid value', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'status' => 'invalid-status',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('denies access when user is not a team admin', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $otherUser = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($otherUser->id);
    $team->assignTeamRole($otherUser, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'name' => 'New Name',
    ]);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Only team admins can update team settings',
        ]);
});

it('denies access by public_id when user is not a team admin', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $otherUser = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($otherUser->id);
    $team->assignTeamRole($otherUser, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->public_id}", [
        'name' => 'New Name',
    ]);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Only team admins can update team settings',
        ]);
});

it('returns 404 for non-existent team', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/teams/99999', [
        'name' => 'New Name',
    ]);

    $response->assertNotFound();
});

it('returns 404 for non-existent public_id', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/teams/nonexistent-public-id', [
        'name' => 'New Name',
    ]);

    $response->assertNotFound();
});

it('denies access to unauthenticated users', function (): void {
    $team = Team::factory()->create();

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'name' => 'New Name',
    ]);

    $response->assertUnauthorized();
});

it('updates multiple fields at once', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create([
        'label' => 'Old Name',
        'description' => 'Old description',
        'status' => 1,
    ]);
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'name' => 'New Name',
        'description' => 'New description',
        'status' => 'inactive',
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'label' => 'New Name',
        'description' => 'New description',
    ]);

    $team->refresh();
    expect($team->status->key())->toBe('inactive');
});

it('does not update key when name is changed', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create(['label' => 'Old Team Name']);
    $originalKey = $team->key;
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->id}", [
        'name' => 'New Team Name',
    ]);

    $response->assertSuccessful();

    $team->refresh();
    expect($team->key)->toBe($originalKey);
});
