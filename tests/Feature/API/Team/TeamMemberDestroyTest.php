<?php

use App\Models\Account\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('allows admin to remove a member', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    $member = User::factory()->create();
    $team->users()->attach($member->id, ['is_admin' => false]);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}/members/{$member->id}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Member removed successfully',
        ]);

    expect($team->users()->where('users.id', $member->id)->exists())->toBeFalse();
});

it('prevents admin from removing themselves', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}/members/{$admin->id}");

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'You cannot remove yourself from the team',
        ]);

    expect($team->users()->where('users.id', $admin->id)->exists())->toBeTrue();
});

it('denies non-admin from removing members', function (): void {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    Sanctum::actingAs($member);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);
    $team->users()->attach($member->id, ['is_admin' => false]);

    $otherMember = User::factory()->create();
    $team->users()->attach($otherMember->id, ['is_admin' => false]);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}/members/{$otherMember->id}");

    $response->assertForbidden();
});

it('returns 404 when member does not exist', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}/members/99999");

    $response->assertNotFound();
});

it('prevents removing the team owner', function (): void {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->ownedBy($owner)->create();
    $team->users()->attach($owner->id, ['is_admin' => true]);
    $team->users()->attach($admin->id, ['is_admin' => true]);

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}/members/{$owner->id}");

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Transfer ownership before removing the owner',
        ]);

    expect($team->users()->where('users.id', $owner->id)->exists())->toBeTrue();
});

it('denies access to unauthenticated users', function (): void {
    $team = Team::factory()->create();

    $response = $this->deleteJson("/api/v1/teams/{$team->public_id}/members/1");

    $response->assertUnauthorized();
});
