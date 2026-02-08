<?php

use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('allows admin to invite a member', function (): void {
    Notification::fake();

    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

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

it('sends notification to existing user', function (): void {
    Notification::fake();

    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    $invitee = User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'existing@example.com',
        'role' => 'member',
    ]);

    $response->assertCreated();

    Notification::assertSentTo($invitee, TeamInvitationNotification::class);
});

it('links invitation to existing user', function (): void {
    Notification::fake();

    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

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
    Notification::fake();

    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

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
    $team->users()->attach($admin->id, ['is_admin' => true]);

    $member = User::factory()->create();
    $team->users()->attach($member->id, ['is_admin' => false]);

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

it('denies non-admin from inviting', function (): void {
    $member = User::factory()->create();
    Sanctum::actingAs($member);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);
    $team->users()->attach($member->id, ['is_admin' => false]);

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
    $team->users()->attach($admin->id, ['is_admin' => true]);

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
    $team->users()->attach($admin->id, ['is_admin' => true]);

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
    $team->users()->attach($admin->id, ['is_admin' => true]);

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
    $team->users()->attach($admin->id, ['is_admin' => true]);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'new@example.com',
        'role' => 'owner',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('denies access to unauthenticated users', function (): void {
    $team = Team::factory()->create();

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'new@example.com',
        'role' => 'member',
    ]);

    $response->assertUnauthorized();
});
