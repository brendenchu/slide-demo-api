<?php

use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns matching users by name', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['is_admin' => true]);

    $matchingUser = User::factory()->create(['name' => 'Zxyqwk Unique', 'email' => 'zxyqwk@example.com']);
    User::factory()->create(['name' => 'Bob Jones', 'email' => 'bob@example.com']);

    $response = $this->getJson('/api/v1/users/search?' . http_build_query([
        'q' => 'Zxyqwk',
        'team_id' => $team->public_id,
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment([
            'name' => 'Zxyqwk Unique',
            'email' => 'zxyqwk@example.com',
        ]);
});

it('returns matching users by email', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['is_admin' => true]);

    $matchingUser = User::factory()->create(['email' => 'findme@example.com']);
    User::factory()->create(['email' => 'notme@other.com']);

    $response = $this->getJson('/api/v1/users/search?' . http_build_query([
        'q' => 'findme',
        'team_id' => $team->public_id,
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment([
            'email' => 'findme@example.com',
        ]);
});

it('excludes current team members', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['is_admin' => true]);

    $member = User::factory()->create(['name' => 'Team Member']);
    $team->users()->attach($member->id, ['is_admin' => false]);

    $nonMember = User::factory()->create(['name' => 'Team Outsider']);

    $response = $this->getJson('/api/v1/users/search?' . http_build_query([
        'q' => 'Team',
        'team_id' => $team->public_id,
    ]));

    $response->assertSuccessful();

    $emails = collect($response->json('data'))->pluck('email')->all();
    expect($emails)->not->toContain($member->email)
        ->and($emails)->toContain($nonMember->email);
});

it('excludes users with pending invitations', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['is_admin' => true]);

    $invitedUser = User::factory()->create(['name' => 'Invited User']);
    TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $user->id,
        'email' => $invitedUser->email,
    ]);

    $availableUser = User::factory()->create(['name' => 'Invited Nobody']);

    $response = $this->getJson('/api/v1/users/search?' . http_build_query([
        'q' => 'Invited',
        'team_id' => $team->public_id,
    ]));

    $response->assertSuccessful();

    $emails = collect($response->json('data'))->pluck('email')->all();
    expect($emails)->not->toContain($invitedUser->email)
        ->and($emails)->toContain($availableUser->email);
});

it('requires authentication', function (): void {
    $team = Team::factory()->create();

    $response = $this->getJson('/api/v1/users/search?' . http_build_query([
        'q' => 'test',
        'team_id' => $team->public_id,
    ]));

    $response->assertUnauthorized();
});

it('requires minimum query length of 2', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();

    $response = $this->getJson('/api/v1/users/search?' . http_build_query([
        'q' => 'a',
        'team_id' => $team->public_id,
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['q']);
});

it('requires team_id parameter', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/users/search?' . http_build_query([
        'q' => 'test',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_id']);
});

it('returns at most 10 results', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['is_admin' => true]);

    User::factory()->count(15)->create(['name' => 'Searchable User']);

    $response = $this->getJson('/api/v1/users/search?' . http_build_query([
        'q' => 'Searchable',
        'team_id' => $team->public_id,
    ]));

    $response->assertSuccessful();
    expect(count($response->json('data')))->toBeLessThanOrEqual(10);
});
