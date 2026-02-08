<?php

use App\Enums\Role;
use App\Enums\Story\ProjectStatus;
use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    foreach (Role::cases() as $role) {
        SpatieRole::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('can complete own project', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $project->update([
        'status' => ProjectStatus::InProgress,
        'current_step' => 'step3',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/complete');

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Project completed successfully',
            'data' => [
                'id' => $project->public_id,
                'status' => 'completed',
                'current_step' => 'complete',
            ],
        ]);

    $project->refresh();
    expect($project->status)->toBe(ProjectStatus::Completed);
    expect($project->current_step)->toBe('complete');
});

it('requires authentication to complete project', function (): void {
    $project = Project::factory()->create();

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/complete');

    $response->assertUnauthorized();
});

it('cannot complete another users project', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/complete');

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'You do not have access to this project',
        ]);
});

it('returns 404 for non-existent project', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/non-existent-id/complete');

    $response->assertNotFound();
});

it('can complete draft project', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/complete');

    $response->assertSuccessful();

    $project->refresh();
    expect($project->status)->toBe(ProjectStatus::Completed);
});

it('can complete project that is already in progress', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $project->update(['status' => ProjectStatus::InProgress]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/complete');

    $response->assertSuccessful();

    $project->refresh();
    expect($project->status)->toBe(ProjectStatus::Completed);
});

it('can complete already completed project', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $project->update([
        'status' => ProjectStatus::Completed,
        'current_step' => 'complete',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/complete');

    $response->assertSuccessful();

    $project->refresh();
    expect($project->status)->toBe(ProjectStatus::Completed);
    expect($project->current_step)->toBe('complete');
});

it('sets current step to complete', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'current_step' => 'step5',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/complete');

    $response->assertSuccessful();

    $project->refresh();
    expect($project->current_step)->toBe('complete');
});

it('preserves project responses when completing', function (): void {
    $user = User::factory()->create();
    $responses = [
        'step1' => ['question1' => 'answer1'],
        'step2' => ['question2' => 'answer2'],
    ];
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'responses' => $responses,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/complete');

    $response->assertSuccessful();

    $project->refresh();
    expect($project->responses)->toBe($responses);
});

it('preserves project title and description when completing', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'label' => 'Important Project',
        'description' => 'Project description',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/complete');

    $response->assertSuccessful();

    $project->refresh();
    expect($project->label)->toBe('Important Project');
    expect($project->description)->toBe('Project description');
});

it('returns complete project data in response', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'label' => 'Test Project',
        'responses' => ['step1' => ['q' => 'a']],
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/complete');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'description',
                'status',
                'current_step',
                'responses',
            ],
        ])
        ->assertJson([
            'data' => [
                'title' => 'Test Project',
                'status' => 'completed',
                'current_step' => 'complete',
            ],
        ]);
});
