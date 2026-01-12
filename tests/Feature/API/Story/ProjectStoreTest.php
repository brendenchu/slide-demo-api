<?php

use App\Enums\Story\ProjectStatus;
use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can create a project', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects', [
        'title' => 'My New Project',
        'description' => 'This is a test project',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'status',
                'current_step',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Project created successfully',
            'data' => [
                'title' => 'My New Project',
                'status' => 'draft',
                'current_step' => 'intro',
            ],
        ]);

    $this->assertDatabaseHas('projects', [
        'user_id' => $user->id,
        'label' => 'My New Project',
        'description' => 'This is a test project',
        'status' => ProjectStatus::Draft->value,
        'current_step' => 'intro',
    ]);
});

it('requires authentication to create project', function (): void {
    $response = $this->postJson('/api/v1/projects', [
        'title' => 'Test Project',
        'description' => 'Description',
    ]);

    $response->assertUnauthorized();
});

it('validates required title field', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects', [
        'description' => 'Description',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

it('validates title must be string', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects', [
        'title' => 123,
        'description' => 'Description',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

it('validates title max length', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects', [
        'title' => str_repeat('a', 256),
        'description' => 'Description',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

it('can create project with nullable description', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects', [
        'title' => 'Project Without Description',
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Project Without Description',
            ],
        ]);
});

it('can create project with null description', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects', [
        'title' => 'Project With Null Description',
        'description' => null,
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Project With Null Description',
            ],
        ]);
});

it('validates description must be string when provided', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects', [
        'title' => 'Test Project',
        'description' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['description']);
});

it('creates project with draft status by default', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects', [
        'title' => 'Draft Project',
        'description' => 'This should be draft',
    ]);

    $response->assertCreated();

    $project = Project::where('label', 'Draft Project')->first();
    expect($project->status)->toBe(ProjectStatus::Draft);
});

it('creates project with intro step by default', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects', [
        'title' => 'New Project',
        'description' => 'Description',
    ]);

    $response->assertCreated();

    $project = Project::where('label', 'New Project')->first();
    expect($project->current_step)->toBe('intro');
});

it('creates project with empty responses by default', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects', [
        'title' => 'New Project',
        'description' => 'Description',
    ]);

    $response->assertCreated();

    $project = Project::where('label', 'New Project')->first();
    expect($project->responses)->toBe([]);
});

it('assigns project to authenticated user', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects', [
        'title' => 'User Project',
        'description' => 'Description',
    ]);

    $response->assertCreated();

    $project = Project::where('label', 'User Project')->first();
    expect($project->user_id)->toBe($user->id);
});
