<?php

use App\Enums\Account\TeamRole;
use App\Enums\Story\ProjectStatus;
use App\Models\Account\Team;
use App\Models\Notification;
use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns notifications for authenticated user', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Notification::factory()->count(3)->create(['recipient_id' => $user->id]);
    Notification::factory()->count(2)->create(); // other user's notifications

    $response = $this->getJson('/api/v1/notifications');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data.notifications')
        ->assertJsonStructure([
            'data' => [
                'notifications' => [
                    '*' => ['id', 'title', 'content', 'type', 'link', 'read_at', 'created_at'],
                ],
                'unread_count',
            ],
        ]);
});

it('does not return other users notifications', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Sanctum::actingAs($user);

    Notification::factory()->count(2)->create(['recipient_id' => $otherUser->id]);

    $response = $this->getJson('/api/v1/notifications');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data.notifications')
        ->assertJsonPath('data.unread_count', 0);
});

it('returns correct unread count', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Notification::factory()->count(3)->create(['recipient_id' => $user->id]);
    Notification::factory()->read()->count(2)->create(['recipient_id' => $user->id]);

    $response = $this->getJson('/api/v1/notifications');

    $response->assertSuccessful()
        ->assertJsonPath('data.unread_count', 3);
});

it('marks a single notification as read', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $notification = Notification::factory()->create(['recipient_id' => $user->id]);

    $response = $this->postJson("/api/v1/notifications/{$notification->public_id}/read");

    $response->assertSuccessful();

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('cannot mark another users notification as read', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Sanctum::actingAs($user);

    $notification = Notification::factory()->create(['recipient_id' => $otherUser->id]);

    $response = $this->postJson("/api/v1/notifications/{$notification->public_id}/read");

    $response->assertNotFound();
});

it('marks all notifications as read', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Notification::factory()->count(3)->create(['recipient_id' => $user->id]);

    $response = $this->postJson('/api/v1/notifications/read-all');

    $response->assertSuccessful();

    $unreadCount = Notification::where('recipient_id', $user->id)->whereNull('read_at')->count();
    expect($unreadCount)->toBe(0);
});

it('creates notification on story completion', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $project = Project::factory()->create([
        'user_id' => $user->id,
        'status' => ProjectStatus::InProgress,
    ]);

    $response = $this->postJson("/api/v1/projects/{$project->public_id}/complete");

    $response->assertSuccessful();

    $this->assertDatabaseHas('notifications', [
        'recipient_id' => $user->id,
        'type' => 'story_completed',
    ]);
});

it('creates notification on team invitation for existing user', function (): void {
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $team = Team::factory()->create();
    $team->users()->attach($admin->id);
    $team->assignTeamRole($admin, TeamRole::Admin);

    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $response = $this->postJson("/api/v1/teams/{$team->public_id}/invitations", [
        'email' => 'invitee@example.com',
        'role' => 'member',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('notifications', [
        'recipient_id' => $invitee->id,
        'sender_id' => $admin->id,
        'type' => 'team_invitation',
    ]);
});

it('denies unauthenticated access to notifications', function (): void {
    $response = $this->getJson('/api/v1/notifications');

    $response->assertUnauthorized();
});
