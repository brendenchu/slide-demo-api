<?php

use App\Models\Account\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('allows owner to transfer ownership to a member', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->ownedBy($owner)->create();
    $team->users()->attach($owner->id, ['is_admin' => true]);

    $member = User::factory()->create();
    $team->users()->attach($member->id, ['is_admin' => false]);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => $member->id,
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Ownership transferred successfully',
        ]);

    $team->refresh();
    expect($team->owner_id)->toBe($member->id);
});

it('denies non-owner admin from transferring ownership', function (): void {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->ownedBy($owner)->create();
    $team->users()->attach($owner->id, ['is_admin' => true]);
    $team->users()->attach($admin->id, ['is_admin' => true]);

    $member = User::factory()->create();
    $team->users()->attach($member->id, ['is_admin' => false]);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => $member->id,
    ]);

    $response->assertForbidden();
});

it('denies regular member from transferring ownership', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    Sanctum::actingAs($member);

    $team = Team::factory()->ownedBy($owner)->create();
    $team->users()->attach($owner->id, ['is_admin' => true]);
    $team->users()->attach($member->id, ['is_admin' => false]);

    $anotherMember = User::factory()->create();
    $team->users()->attach($anotherMember->id, ['is_admin' => false]);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => $anotherMember->id,
    ]);

    $response->assertForbidden();
});

it('rejects transfer to non-member', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->ownedBy($owner)->create();
    $team->users()->attach($owner->id, ['is_admin' => true]);

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

it('ensures new owner gets admin privileges on pivot', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->ownedBy($owner)->create();
    $team->users()->attach($owner->id, ['is_admin' => true]);

    $member = User::factory()->create();
    $team->users()->attach($member->id, ['is_admin' => false]);

    $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => $member->id,
    ]);

    $pivot = $team->users()->where('users.id', $member->id)->first()->pivot;
    expect((bool) $pivot->is_admin)->toBeTrue();
});

it('keeps previous owner as admin member', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->ownedBy($owner)->create();
    $team->users()->attach($owner->id, ['is_admin' => true]);

    $member = User::factory()->create();
    $team->users()->attach($member->id, ['is_admin' => false]);

    $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", [
        'user_id' => $member->id,
    ]);

    $pivot = $team->users()->where('users.id', $owner->id)->first()->pivot;
    expect((bool) $pivot->is_admin)->toBeTrue();
});

it('validates user_id is required', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->ownedBy($owner)->create();
    $team->users()->attach($owner->id, ['is_admin' => true]);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/transfer-ownership", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user_id']);
});

it('validates user_id exists', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $team = Team::factory()->ownedBy($owner)->create();
    $team->users()->attach($owner->id, ['is_admin' => true]);

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
