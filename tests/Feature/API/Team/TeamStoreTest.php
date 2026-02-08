<?php

use App\Models\Account\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates a new team', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'New Team',
        'description' => 'This is a new team',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'status',
                'created_at',
                'updated_at',
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'New Team',
                'status' => 'active',
            ],
            'message' => 'Team created successfully',
        ]);

    $this->assertDatabaseHas('teams', [
        'label' => 'New Team',
        'description' => 'This is a new team',
    ]);
});

it('adds authenticated user to created team as admin', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'New Team',
    ]);

    $response->assertCreated();

    $team = Team::where('label', 'New Team')->first();
    expect($team->users->contains($user))->toBeTrue();
    expect($team->isAdmin($user))->toBeTrue();
});

it('sets creator as team owner', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'New Team',
    ]);

    $response->assertCreated();

    $team = Team::where('label', 'New Team')->first();
    expect($team->owner_id)->toBe($user->id);
    expect($team->isOwner($user))->toBeTrue();
});

it('creates team with active status by default', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'New Team',
    ]);

    $response->assertCreated();

    $team = Team::where('label', 'New Team')->first();
    expect($team->status->key())->toBe('active');
});

it('creates team with specified active status', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'New Team',
        'status' => 'active',
    ]);

    $response->assertCreated();

    $team = Team::where('label', 'New Team')->first();
    expect($team->status->key())->toBe('active');
});

it('creates team with specified inactive status', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'New Team',
        'status' => 'inactive',
    ]);

    $response->assertCreated();

    $team = Team::where('label', 'New Team')->first();
    expect($team->status->key())->toBe('inactive');
});

it('creates team without description', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'New Team',
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'New Team',
            ],
        ]);

    $this->assertDatabaseHas('teams', [
        'label' => 'New Team',
    ]);
});

it('generates key from team name', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'My Awesome Team',
    ]);

    $response->assertCreated();

    $team = Team::where('label', 'My Awesome Team')->first();
    expect($team->key)->toContain('my-awesome-team');
});

it('validates required name field', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('validates name is string', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('validates name max length', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => str_repeat('a', 256),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('validates description is string', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'New Team',
        'description' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['description']);
});

it('validates status is valid value', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'New Team',
        'status' => 'invalid-status',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('allows valid status values', function (string $status): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => "Team {$status}",
        'status' => $status,
    ]);

    $response->assertCreated();

    $team = Team::where('label', "Team {$status}")->first();
    expect($team->status->key())->toBe($status);
})->with(['active', 'inactive']);

it('denies access to unauthenticated users', function (): void {
    $response = $this->postJson('/api/v1/teams', [
        'name' => 'New Team',
    ]);

    $response->assertUnauthorized();
});

it('creates multiple teams for same user', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response1 = $this->postJson('/api/v1/teams', [
        'name' => 'Team One',
    ]);

    $response2 = $this->postJson('/api/v1/teams', [
        'name' => 'Team Two',
    ]);

    $response1->assertCreated();
    $response2->assertCreated();

    $this->assertDatabaseHas('teams', ['label' => 'Team One']);
    $this->assertDatabaseHas('teams', ['label' => 'Team Two']);
});
