<?php

use App\Enums\Role;
use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('demo.enabled', true);

    foreach (Role::cases() as $role) {
        SpatieRole::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }
});

// --- Registration Limits ---

it('blocks registration when user limit is reached', function (): void {
    config()->set('demo.limits.max_users', 2);

    User::factory()->count(2)->create();

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Demo limit reached: maximum of 2 user accounts.',
        ]);
});

it('allows registration when under user limit', function (): void {
    config()->set('demo.limits.max_users', 5);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated();
});

it('allows registration when demo mode is off', function (): void {
    config()->set('demo.enabled', false);
    config()->set('demo.limits.max_users', 1);

    User::factory()->create();

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated();
});

// --- Admin User Creation Limits ---

it('blocks admin user creation when user limit is reached', function (): void {
    config()->set('demo.limits.max_users', 2);

    $admin = User::factory()->create();
    $admin->assignRole(Role::SuperAdmin->value);
    Sanctum::actingAs($admin);

    // admin + 1 more = 2 total
    User::factory()->create();

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'New Admin User',
        'email' => 'newadmin@example.com',
        'password' => 'password123',
        'role' => 'client',
    ]);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Demo limit reached: maximum of 2 user accounts.',
        ]);
});

// --- Team Creation Limits ---

it('blocks team creation when team limit per user is reached', function (): void {
    config()->set('demo.limits.max_teams_per_user', 1);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // User already has personal team from factory, create one non-personal
    Team::factory()->ownedBy($user)->create(['is_personal' => false]);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'Another Team',
    ]);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Demo limit reached: maximum of 1 teams per user.',
        ]);
});

it('allows team creation when under team limit', function (): void {
    config()->set('demo.limits.max_teams_per_user', 5);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/teams', [
        'name' => 'New Team',
    ]);

    $response->assertCreated();
});

it('does not count personal teams toward team limit', function (): void {
    config()->set('demo.limits.max_teams_per_user', 1);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // User has a personal team from factory, but limit only counts non-personal
    $response = $this->postJson('/api/v1/teams', [
        'name' => 'First Non-Personal Team',
    ]);

    $response->assertCreated();
});

// --- Project Creation Limits ---

it('blocks project creation when project limit per team is reached', function (): void {
    config()->set('demo.limits.max_projects_per_team', 2);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = $user->currentTeam();

    // Attach 2 projects to the team
    $projects = Project::factory()->count(2)->create(['user_id' => $user->id]);
    foreach ($projects as $project) {
        $team->projects()->attach($project->id);
    }

    $response = $this->postJson('/api/v1/projects', [
        'title' => 'One Too Many',
        'description' => 'Should be blocked',
    ]);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Demo limit reached: maximum of 2 projects per team.',
        ]);
});

it('allows project creation when under project limit', function (): void {
    config()->set('demo.limits.max_projects_per_team', 10);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects', [
        'title' => 'New Project',
        'description' => 'Description',
    ]);

    $response->assertCreated();
});

// --- Invitation Limits ---

it('blocks invitation creation when invitation limit per team is reached', function (): void {
    Notification::fake();
    config()->set('demo.limits.max_invitations_per_team', 1);

    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->ownedBy($admin)->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $admin->id,
    ]);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'new@example.com',
        'role' => 'member',
    ]);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Demo limit reached: maximum of 1 pending invitations per team.',
        ]);
});

it('allows invitation creation when under invitation limit', function (): void {
    Notification::fake();
    config()->set('demo.limits.max_invitations_per_team', 10);

    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->ownedBy($admin)->create();
    $team->users()->attach($admin->id, ['is_admin' => true]);

    User::factory()->create(['email' => 'new@example.com']);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'new@example.com',
        'role' => 'member',
    ]);

    $response->assertCreated();
});

// --- Non-POST routes pass through ---

it('allows GET requests even when demo mode is on', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects');

    $response->assertSuccessful();
});
