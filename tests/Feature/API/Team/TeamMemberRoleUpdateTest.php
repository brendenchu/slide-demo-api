<?php

use App\Enums\Account\TeamRole;
use App\Models\Account\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('allows admin to promote member to admin', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $member = User::factory()->create();
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $response = $this->putJson("/api/v1/teams/{$team->public_id}/members/{$member->id}/role", [
        'role' => 'admin',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Member role updated successfully',
        ]);

    expect($team->isAdmin($member))->toBeTrue();
});

it('allows admin to demote admin to member', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $otherAdmin = User::factory()->create();
    $team->users()->attach($otherAdmin->id);
    $team->assignTeamRole($otherAdmin, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->public_id}/members/{$otherAdmin->id}/role", [
        'role' => 'member',
    ]);

    $response->assertSuccessful();

    expect($team->isAdmin($otherAdmin))->toBeFalse();
});

it('prevents admin from changing their own role', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->public_id}/members/{$admin->id}/role", [
        'role' => 'member',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'You cannot change your own role',
        ]);
});

it('denies non-admin from updating roles', function (): void {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    Sanctum::actingAs($member);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $response = $this->putJson("/api/v1/teams/{$team->public_id}/members/{$admin->id}/role", [
        'role' => 'member',
    ]);

    $response->assertForbidden();
});

it('validates role field is required', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $member = User::factory()->create();
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $response = $this->putJson("/api/v1/teams/{$team->public_id}/members/{$member->id}/role", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('validates role must be admin or member', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $member = User::factory()->create();
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $response = $this->putJson("/api/v1/teams/{$team->public_id}/members/{$member->id}/role", [
        'role' => 'owner',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('prevents changing the owner role', function (): void {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->withOwner($owner)->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $response = $this->putJson("/api/v1/teams/{$team->public_id}/members/{$owner->id}/role", [
        'role' => 'member',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Owner role cannot be changed',
        ]);
});

it('denies access to unauthenticated users', function (): void {
    $team = Team::factory()->create();

    $response = $this->putJson("/api/v1/teams/{$team->public_id}/members/1/role", [
        'role' => 'admin',
    ]);

    $response->assertUnauthorized();
});
