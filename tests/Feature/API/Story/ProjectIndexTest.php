<?php

use App\Enums\Story\ProjectStatus;
use App\Models\Account\Team;
use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

/**
 * Create a project attached to the user's current team.
 *
 * @param  array<string, mixed>  $attributes
 */
function createProjectForUser(User $user, array $attributes = []): Project
{
    $project = Project::factory()->create(array_merge(['user_id' => $user->id], $attributes));
    $currentTeam = $user->currentTeam();
    if ($currentTeam) {
        $project->teams()->attach($currentTeam->id);
    }

    return $project;
}

it('can list authenticated users projects', function (): void {
    $user = User::factory()->create();
    createProjectForUser($user);
    createProjectForUser($user);
    createProjectForUser($user);
    Project::factory()->count(2)->create(); // Other user's projects

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'status',
                    'current_step',
                ],
            ],
        ])
        ->assertJsonCount(3, 'data');
});

it('requires authentication to list projects', function (): void {
    $response = $this->getJson('/api/v1/projects');

    $response->assertUnauthorized();
});

it('can filter projects by draft status', function (): void {
    $user = User::factory()->create();
    createProjectForUser($user); // Draft by default
    $inProgress = createProjectForUser($user);
    $inProgress->update(['status' => ProjectStatus::InProgress->value]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?status=draft');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('can filter projects by in_progress status', function (): void {
    $user = User::factory()->create();
    createProjectForUser($user); // Draft by default
    $inProgress = createProjectForUser($user);
    $inProgress->update(['status' => ProjectStatus::InProgress->value]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?status=in_progress');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('can filter projects by completed status', function (): void {
    $user = User::factory()->create();
    createProjectForUser($user); // Draft by default
    $completed = createProjectForUser($user);
    $completed->update(['status' => ProjectStatus::Completed->value]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?status=completed');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('ignores invalid status filter values', function (): void {
    $user = User::factory()->create();
    createProjectForUser($user);
    createProjectForUser($user);
    createProjectForUser($user);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?status=invalid');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('can search projects by label', function (): void {
    $user = User::factory()->create();
    createProjectForUser($user, ['label' => 'My Special Project']);
    createProjectForUser($user, ['label' => 'Another Project']);
    createProjectForUser($user, ['label' => 'Third Project']);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?search=Special');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'My Special Project');
});

it('returns empty array when no projects match search', function (): void {
    $user = User::factory()->create();
    createProjectForUser($user);
    createProjectForUser($user);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?search=NonExistent');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

it('returns projects ordered by updated_at descending', function (): void {
    $user = User::factory()->create();
    createProjectForUser($user, [
        'label' => 'Old Project',
        'updated_at' => now()->subDays(5),
    ]);
    createProjectForUser($user, [
        'label' => 'New Project',
        'updated_at' => now(),
    ]);
    createProjectForUser($user, [
        'label' => 'Middle Project',
        'updated_at' => now()->subDays(2),
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.title', 'New Project')
        ->assertJsonPath('data.1.title', 'Middle Project')
        ->assertJsonPath('data.2.title', 'Old Project');
});

it('can combine status filter and search', function (): void {
    $user = User::factory()->create();
    createProjectForUser($user, ['label' => 'Draft Special']); // Draft by default
    $inProgress = createProjectForUser($user, ['label' => 'Special Progress']);
    $inProgress->update(['status' => ProjectStatus::InProgress->value]);
    createProjectForUser($user, ['label' => 'Other Draft']); // Draft by default

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?status=draft&search=Special');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Draft Special');
});

it('returns empty array when user has no projects', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

it('scopes projects to current team', function (): void {
    $user = User::factory()->create();
    $teamA = Team::factory()->create();
    $teamB = Team::factory()->create();

    $user->teams()->attach($teamA->id, ['is_admin' => true]);
    $user->teams()->attach($teamB->id, ['is_admin' => false]);
    $user->setSetting('current_team', $teamA->key);

    $projectA = Project::factory()->create(['user_id' => $user->id]);
    $projectA->teams()->sync([$teamA->id]);

    $projectB = Project::factory()->create(['user_id' => $user->id]);
    $projectB->teams()->sync([$teamB->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $projectA->public_id);
});

it('returns empty results when current team has no projects', function (): void {
    $user = User::factory()->create();
    $teamA = Team::factory()->create();
    $teamB = Team::factory()->create();

    $user->teams()->attach($teamA->id, ['is_admin' => true]);
    $user->teams()->attach($teamB->id, ['is_admin' => false]);
    $user->setSetting('current_team', $teamB->key);

    $project = Project::factory()->create(['user_id' => $user->id]);
    $project->teams()->sync([$teamA->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});
