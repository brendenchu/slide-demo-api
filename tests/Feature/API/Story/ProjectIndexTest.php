<?php

use App\Enums\Story\ProjectStatus;
use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can list authenticated users projects', function (): void {
    $user = User::factory()->create();
    Project::factory()->count(3)->create(['user_id' => $user->id]);
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
    Project::factory()->create([
        'user_id' => $user->id,
    ]); // Will be Draft by default
    $inProgress = Project::factory()->create([
        'user_id' => $user->id,
    ]);
    $inProgress->update(['status' => ProjectStatus::InProgress->value]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?status=draft');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('can filter projects by in_progress status', function (): void {
    $user = User::factory()->create();
    Project::factory()->create([
        'user_id' => $user->id,
    ]); // Will be Draft by default
    $inProgress = Project::factory()->create([
        'user_id' => $user->id,
    ]);
    $inProgress->update(['status' => ProjectStatus::InProgress->value]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?status=in_progress');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('can filter projects by completed status', function (): void {
    $user = User::factory()->create();
    Project::factory()->create([
        'user_id' => $user->id,
    ]); // Will be Draft by default
    $completed = Project::factory()->create([
        'user_id' => $user->id,
    ]);
    $completed->update(['status' => ProjectStatus::Completed->value]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?status=completed');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('ignores invalid status filter values', function (): void {
    $user = User::factory()->create();
    Project::factory()->count(3)->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?status=invalid');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('can search projects by label', function (): void {
    $user = User::factory()->create();
    Project::factory()->create([
        'user_id' => $user->id,
        'label' => 'My Special Project',
    ]);
    Project::factory()->create([
        'user_id' => $user->id,
        'label' => 'Another Project',
    ]);
    Project::factory()->create([
        'user_id' => $user->id,
        'label' => 'Third Project',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?search=Special');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'My Special Project');
});

it('returns empty array when no projects match search', function (): void {
    $user = User::factory()->create();
    Project::factory()->count(2)->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects?search=NonExistent');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

it('returns projects ordered by updated_at descending', function (): void {
    $user = User::factory()->create();
    $oldProject = Project::factory()->create([
        'user_id' => $user->id,
        'label' => 'Old Project',
        'updated_at' => now()->subDays(5),
    ]);
    $newProject = Project::factory()->create([
        'user_id' => $user->id,
        'label' => 'New Project',
        'updated_at' => now(),
    ]);
    $middleProject = Project::factory()->create([
        'user_id' => $user->id,
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
    Project::factory()->create([
        'user_id' => $user->id,
        'label' => 'Draft Special',
    ]); // Will be Draft by default
    $inProgress = Project::factory()->create([
        'user_id' => $user->id,
        'label' => 'Special Progress',
    ]);
    $inProgress->update(['status' => ProjectStatus::InProgress->value]);
    Project::factory()->create([
        'user_id' => $user->id,
        'label' => 'Other Draft',
    ]); // Will be Draft by default

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
