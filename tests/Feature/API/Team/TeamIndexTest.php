<?php

use App\Models\Account\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns teams for authenticated user', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/teams');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
        ]);
});

it('returns only teams user belongs to', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $userTeam = $user->teams()->first();

    $otherUser = User::factory()->create();
    $otherTeam = $otherUser->teams()->first();

    $response = $this->getJson('/api/v1/teams');

    $response->assertSuccessful()
        ->assertJsonFragment([
            'id' => $userTeam->public_id,
        ])
        ->assertJsonMissing([
            'id' => $otherTeam->public_id,
        ]);
});

it('includes teams user is added to', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $team->users()->attach($user->id);

    $response = $this->getJson('/api/v1/teams');

    $response->assertSuccessful();

    $data = $response->json('data');
    $teamIds = array_column($data, 'id');

    expect($teamIds)->toContain($team->public_id);
});

it('returns empty array when user has no teams except default', function (): void {
    $user = User::factory()->create();

    $user->teams()->detach();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/teams');

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [],
        ]);
});

it('denies access to unauthenticated users', function (): void {
    $response = $this->getJson('/api/v1/teams');

    $response->assertUnauthorized();
});

it('returns multiple teams when user belongs to multiple teams', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team1 = Team::factory()->create(['label' => 'Team One']);
    $team2 = Team::factory()->create(['label' => 'Team Two']);

    $user->teams()->attach([$team1->id, $team2->id]);

    $response = $this->getJson('/api/v1/teams');

    $response->assertSuccessful();

    $data = $response->json('data');
    expect(count($data))->toBeGreaterThanOrEqual(2);
});
