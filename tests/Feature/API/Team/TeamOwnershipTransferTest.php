<?php

use App\Enums\Account\TeamRole;
use App\Models\Account\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('allows owner to transfer ownership to a member', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->withOwner($owner)->create();

    $member = User::factory()->create();
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => $member->id,
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Ownership transferred successfully',
        ]);

    expect($team->isOwner($member))->toBeTrue();
});

it('denies non-owner admin from transferring ownership', function (): void {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->withOwner($owner)->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $member = User::factory()->create();
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => $member->id,
    ]);

    $response->assertForbidden();
});

it('denies regular member from transferring ownership', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    Sanctum::actingAs($member);

    $team = Team::factory()->withOwner($owner)->create();
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $anotherMember = User::factory()->create();
    $team->users()->attach($anotherMember->id);
    $team->assignTeamRole($anotherMember, TeamRole::Member);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => $anotherMember->id,
    ]);

    $response->assertForbidden();
});

it('rejects transfer to non-member', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->withOwner($owner)->create();

    $nonMember = User::factory()->create();

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => $nonMember->id,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'The specified user is not a member of this team',
        ]);
});

it('ensures new owner gets owner role', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->withOwner($owner)->create();

    $member = User::factory()->create();
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => $member->id,
    ]);

    expect($team->isOwner($member))->toBeTrue();
    expect($team->isAdmin($member))->toBeTrue();
});

it('keeps previous owner as admin member', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->withOwner($owner)->create();

    $member = User::factory()->create();
    $team->users()->attach($member->id);
    $team->assignTeamRole($member, TeamRole::Member);

    $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => $member->id,
    ]);

    expect($team->isAdmin($owner))->toBeTrue();
    expect($team->isOwner($owner))->toBeFalse();
});

it('validates user_id is required', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->withOwner($owner)->create();

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user_id']);
});

it('validates user_id exists', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->withOwner($owner)->create();

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => 99999,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user_id']);
});

it('denies unauthenticated users', function (): void {
    $team = Team::factory()->create();

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => 1,
    ]);

    $response->assertUnauthorized();
});
