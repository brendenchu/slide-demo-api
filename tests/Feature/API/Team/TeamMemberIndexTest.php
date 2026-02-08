<?php

use App\Enums\Account\TeamRole;
use App\Models\Account\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('lists team members', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $team->users()->attach($user->id);
    $team->assignTeamRole($user, TeamRole::Admin);

    $member = User::factory()->create();
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $response = $this->getJson("/api/v1/teams/{$team->public_id}/members");

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'email', 'is_admin', 'joined_at'],
            ],
        ]);
});

it('shows admin flag correctly', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $member = User::factory()->create();
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $response = $this->getJson("/api/v1/teams/{$team->public_id}/members");

    $response->assertSuccessful();

    $data = $response->json('data');
    $adminData = collect($data)->firstWhere('id', (string) $admin->id);
    $memberData = collect($data)->firstWhere('id', (string) $member->id);

    expect($adminData['is_admin'])->toBeTrue();
    expect($memberData['is_admin'])->toBeFalse();
});

it('denies access to non-members', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $otherUser = User::factory()->create();
    $team->users()->attach($otherUser->id);
    $team->assignTeamRole($otherUser, TeamRole::Admin);

    $response = $this->getJson("/api/v1/teams/{$team->public_id}/members");

    $response->assertForbidden();
});

it('denies access to unauthenticated users', function (): void {
    $team = Team::factory()->create();

    $response = $this->getJson("/api/v1/teams/{$team->public_id}/members");

    $response->assertUnauthorized();
});

it('returns 404 for non-existent team', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/teams/nonexistent/members');

    $response->assertNotFound();
});
