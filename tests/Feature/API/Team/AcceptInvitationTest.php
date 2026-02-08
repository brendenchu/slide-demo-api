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

it('allows user to accept a pending invitation', function (): void {
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);
    Sanctum::actingAs($invitee);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'invitee@example.com',
        'role' => 'member',
    ]);

    $response = $this->postJson("/api/v1/invitations/{$invitation->public_id}/accept");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Invitation accepted successfully',
        ]);

    $invitation->refresh();
    expect($invitation->status)->toBe(InvitationStatus::Accepted);
    expect($invitation->accepted_at)->not->toBeNull();
    expect($team->users()->where('users.id', $invitee->id)->exists())->toBeTrue();
});

it('adds user with correct role when accepting admin invitation', function (): void {
    $invitee = User::factory()->create(['email' => 'admin-invitee@example.com']);
    Sanctum::actingAs($invitee);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $invitation = TeamInvitation::factory()->asAdmin()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'admin-invitee@example.com',
    ]);

    $response = $this->postJson("/api/v1/invitations/{$invitation->public_id}/accept");

    $response->assertSuccessful();

    expect($team->isAdmin($invitee))->toBeTrue();
});

it('rejects expired invitation', function (): void {
    $invitee = User::factory()->create(['email' => 'expired@example.com']);
    Sanctum::actingAs($invitee);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $invitation = TeamInvitation::factory()->expired()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'expired@example.com',
    ]);

    $response = $this->postJson("/api/v1/invitations/{$invitation->public_id}/accept");

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'This invitation has expired',
        ]);
});

it('rejects acceptance by wrong email', function (): void {
    $wrongUser = User::factory()->create(['email' => 'wrong@example.com']);
    Sanctum::actingAs($wrongUser);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'correct@example.com',
    ]);

    $response = $this->postJson("/api/v1/invitations/{$invitation->public_id}/accept");

    $response->assertForbidden();
});

it('rejects if user is already a team member', function (): void {
    $invitee = User::factory()->create(['email' => 'already-member@example.com']);
    Sanctum::actingAs($invitee);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);
    $team->users()->attach($invitee->id);
    $team->assignTeamRole($invitee, TeamRole::Member);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'already-member@example.com',
    ]);

    $response = $this->postJson("/api/v1/invitations/{$invitation->public_id}/accept");

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'You are already a member of this team',
        ]);
});

it('denies access to unauthenticated users', function (): void {
    $response = $this->postJson('/api/v1/invitations/some-id/accept');

    $response->assertUnauthorized();
});
