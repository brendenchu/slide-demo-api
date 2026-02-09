<?php

use App\Enums\Account\TeamRole;
use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('allows admin to invite a member', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $invitee = User::factory()->create(['email' => 'new@example.com']);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'new@example.com',
        'role' => 'member',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'success',
            'data' => ['id', 'email', 'role', 'status', 'expires_at', 'created_at'],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'email' => 'new@example.com',
                'role' => 'member',
                'status' => 'pending',
            ],
            'message' => 'Invitation sent successfully',
        ]);

    $this->assertDatabaseHas('team_invitations', [
        'team_id' => $team->id,
        'email' => 'new@example.com',
        'role' => 'member',
    ]);
});

it('creates notification for existing user', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $invitee = User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'existing@example.com',
        'role' => 'member',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('notifications', [
        'recipient_id' => $invitee->id,
        'sender_id' => $admin->id,
        'type' => 'team_invitation',
    ]);
});

it('links invitation to existing user', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $invitee = User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'existing@example.com',
        'role' => 'member',
    ]);

    $response->assertCreated();

    $invitation = TeamInvitation::where('email', 'existing@example.com')->first();
    expect($invitation->user_id)->toBe($invitee->id);
});

it('prevents duplicate invitations', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    User::factory()->create(['email' => 'duplicate@example.com']);

    TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'duplicate@example.com',
    ]);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'duplicate@example.com',
        'role' => 'member',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'An invitation has already been sent to this email',
        ]);
});

it('prevents inviting existing members', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $member = User::factory()->create();
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => $member->email,
        'role' => 'member',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'This user is already a member of the team',
        ]);
});

it('denies admin from inviting as admin role', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $invitee = User::factory()->create(['email' => 'new@example.com']);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'new@example.com',
        'role' => 'admin',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('allows owner to invite as admin role', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->withOwner($owner)->create();

    $invitee = User::factory()->create(['email' => 'new@example.com']);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'new@example.com',
        'role' => 'admin',
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'email' => 'new@example.com',
                'role' => 'admin',
                'status' => 'pending',
            ],
        ]);
});

it('denies non-admin from inviting', function (): void {
    $member = User::factory()->create();
    Sanctum::actingAs($member);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'new@example.com',
        'role' => 'member',
    ]);

    $response->assertForbidden();
});

it('validates email is required', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'role' => 'member',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('validates email format', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'not-an-email',
        'role' => 'member',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('validates role is required', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'new@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('validates role must be admin or member', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'new@example.com',
        'role' => 'owner',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('rejects non-registered email address', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'nonexistent@example.com',
        'role' => 'member',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('denies access to unauthenticated users', function (): void {
    $team = Team::factory()->create();

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'new@example.com',
        'role' => 'member',
    ]);

    $response->assertUnauthorized();
});
