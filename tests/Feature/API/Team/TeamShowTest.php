<?php

use App\Models\Account\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('shows a team by id when user is a member', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create(['label' => 'My Team']);
    $team->users()->attach($user->id);

    $response = $this->getJson("/api/v1/teams/{$team->id}");

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
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $team->public_id,
                'name' => 'My Team',
            ],
        ]);
});

it('shows a team by public_id when user is a member', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create(['label' => 'My Team']);
    $team->users()->attach($user->id);

    $response = $this->getJson("/api/v1/teams/{$team->public_id}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $team->public_id,
                'name' => 'My Team',
            ],
        ]);
});

it('denies access when user is not a team member', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $otherUser = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($otherUser->id);

    $response = $this->getJson("/api/v1/teams/{$team->id}");

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'You do not have access to this team',
        ]);
});

it('denies access by public_id when user is not a team member', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $otherUser = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($otherUser->id);

    $response = $this->getJson("/api/v1/teams/{$team->public_id}");

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'You do not have access to this team',
        ]);
});

it('returns 404 for non-existent team', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/teams/99999');

    $response->assertNotFound();
});

it('returns 404 for non-existent public_id', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/teams/nonexistent-public-id');

    $response->assertNotFound();
});

it('denies access to unauthenticated users', function (): void {
    $team = Team::factory()->create();

    $response = $this->getJson("/api/v1/teams/{$team->id}");

    $response->assertUnauthorized();
});

it('shows team with all fields', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create([
        'label' => 'Test Team',
        'description' => 'This is a test team',
    ]);
    $team->users()->attach($user->id);

    $response = $this->getJson("/api/v1/teams/{$team->id}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Test Team',
                'status' => 'active',
            ],
        ]);
});
