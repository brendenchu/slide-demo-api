<?php

use App\Enums\Account\InvitationStatus;
use App\Enums\Account\TeamRole;
use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('allows admin to cancel a pending invitation', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
    ]);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}/invitations/{$invitation->public_id}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Invitation cancelled successfully',
        ]);

    $invitation->refresh();
    expect($invitation->status)->toBe(InvitationStatus::Cancelled);
});

it('denies non-admin from cancelling invitations', function (): void {
    $member = User::factory()->create();
    Sanctum::actingAs($member);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
    ]);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}/invitations/{$invitation->public_id}");

    $response->assertForbidden();
});

it('returns 404 for already accepted invitation', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $invitation = TeamInvitation::factory()->accepted()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
    ]);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}/invitations/{$invitation->public_id}");

    $response->assertNotFound();
});

it('denies access to unauthenticated users', function (): void {
    $team = Team::factory()->create();

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}/invitations/some-id");

    $response->assertUnauthorized();
});
