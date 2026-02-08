<?php

use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('lists pending invitations for authenticated user', function (): void {
    $user = User::factory()->create(['email' => 'user@example.com']);
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    TeamInvitation::factory()->count(2)->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'user@example.com',
    ]);

    $response = $this->getJson('/api/v1/invitations');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'email', 'role', 'status', 'team', 'expires_at', 'created_at'],
            ],
        ]);
});

it('does not list invitations for other users', function (): void {
    $user = User::factory()->create(['email' => 'user@example.com']);
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'other@example.com',
    ]);

    $response = $this->getJson('/api/v1/invitations');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

it('does not list accepted invitations', function (): void {
    $user = User::factory()->create(['email' => 'user@example.com']);
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $admin = User::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    TeamInvitation::factory()->accepted()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'user@example.com',
    ]);

    $response = $this->getJson('/api/v1/invitations');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

it('includes team information', function (): void {
    $user = User::factory()->create(['email' => 'user@example.com']);
    Sanctum::actingAs($user);

    $team = Team::factory()->create(['label' => 'Test Team']);
    $admin = User::factory()->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
        'email' => 'user@example.com',
    ]);

    $response = $this->getJson('/api/v1/invitations');

    $response->assertSuccessful();

    $data = $response->json('data.0');
    expect($data['team']['name'])->toBe('Test Team');
});

it('denies access to unauthenticated users', function (): void {
    $response = $this->getJson('/api/v1/invitations');

    $response->assertUnauthorized();
});
