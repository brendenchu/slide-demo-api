<?php

use App\Enums\Account\TeamRole;
use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('lists pending invitations for admin', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    TeamInvitation::factory()->count(3)->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
    ]);

    $response = $this->getJson("/api/v1/teams/{$team->public_id}/invitations");

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'email', 'role', 'status', 'expires_at', 'created_at'],
            ],
        ]);
});

it('only lists pending invitations', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    TeamInvitation::factory()->count(2)->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
    ]);

    TeamInvitation::factory()->accepted()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
    ]);

    $response = $this->getJson("/api/v1/teams/{$team->public_id}/invitations");

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('denies non-admin from viewing invitations', function (): void {
    $member = User::factory()->create();
    Sanctum::actingAs($member);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $response = $this->getJson("/api/v1/teams/{$team->public_id}/invitations");

    $response->assertForbidden();
});

it('denies access to unauthenticated users', function (): void {
    $team = Team::factory()->create();

    $response = $this->getJson("/api/v1/teams/{$team->public_id}/invitations");

    $response->assertUnauthorized();
});
