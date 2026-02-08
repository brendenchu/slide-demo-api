<?php

use App\Enums\Account\InvitationStatus;
use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('allows user to decline a pending invitation', function (): void {
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);
    Sanctum::actingAs($invitee);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'invitee@example.com',
    ]);

    $response = $this->postJson("/api/v1/invitations/{$invitation->public_id}/decline");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Invitation declined successfully',
        ]);

    $invitation->refresh();
    expect($invitation->status)->toBe(InvitationStatus::Declined);
});

it('rejects decline by wrong email', function (): void {
    $wrongUser = User::factory()->create(['email' => 'wrong@example.com']);
    Sanctum::actingAs($wrongUser);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'correct@example.com',
    ]);

    $response = $this->postJson("/api/v1/invitations/{$invitation->public_id}/decline");

    $response->assertForbidden();
});

it('returns 404 for already accepted invitation', function (): void {
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);
    Sanctum::actingAs($invitee);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    $invitation = TeamInvitation::factory()->accepted()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'invitee@example.com',
    ]);

    $response = $this->postJson("/api/v1/invitations/{$invitation->public_id}/decline");

    $response->assertNotFound();
});

it('denies access to unauthenticated users', function (): void {
    $response = $this->postJson('/api/v1/invitations/some-id/decline');

    $response->assertUnauthorized();
});
