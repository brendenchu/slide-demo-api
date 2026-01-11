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
        SpatieRole::create(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('can save responses to project', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $project->update(['current_step' => 'intro']);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'step' => 'step1',
        'responses' => [
            'question1' => 'answer1',
            'question2' => 'answer2',
        ],
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Responses saved successfully',
            'data' => [
                'id' => $project->public_id,
                'current_step' => 'step1',
                'status' => 'in_progress',
                'responses' => [
                    'step1' => [
                        'question1' => 'answer1',
                        'question2' => 'answer2',
                    ],
                ],
            ],
        ]);

    $project->refresh();
    expect($project->status)->toBe(ProjectStatus::InProgress);
    expect($project->current_step)->toBe('step1');
    expect($project->responses)->toBe([
        'step1' => [
            'question1' => 'answer1',
            'question2' => 'answer2',
        ],
    ]);
});

it('requires authentication to save responses', function (): void {
    $project = Project::factory()->create();

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'step' => 'step1',
        'responses' => ['question' => 'answer'],
    ]);

    $response->assertUnauthorized();
});

it('cannot save responses to another users project', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'step' => 'step1',
        'responses' => ['question' => 'hacked'],
    ]);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'You do not have access to this project',
        ]);
});

it('returns 404 for non-existent project', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/non-existent-id/responses', [
        'step' => 'step1',
        'responses' => ['question' => 'answer'],
    ]);

    $response->assertNotFound();
});

it('validates required step field', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'responses' => ['question' => 'answer'],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['step']);
});

it('validates step must be string', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'step' => 123,
        'responses' => ['question' => 'answer'],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['step']);
});

it('validates required responses field', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'step' => 'step1',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['responses']);
});

it('validates responses must be array', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'step' => 'step1',
        'responses' => 'not-an-array',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['responses']);
});

it('can update existing responses for same step', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'responses' => [
            'step1' => [
                'question1' => 'old answer',
            ],
        ],
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'step' => 'step1',
        'responses' => [
            'question1' => 'new answer',
            'question2' => 'additional answer',
        ],
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->responses)->toBe([
        'step1' => [
            'question1' => 'new answer',
            'question2' => 'additional answer',
        ],
    ]);
});

it('can add responses to different steps', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'responses' => [
            'step1' => ['question1' => 'answer1'],
        ],
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'step' => 'step2',
        'responses' => ['question2' => 'answer2'],
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->responses)->toBe([
        'step1' => ['question1' => 'answer1'],
        'step2' => ['question2' => 'answer2'],
    ]);
    expect($project->current_step)->toBe('step2');
});

it('changes project status to in_progress', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'step' => 'step1',
        'responses' => ['question' => 'answer'],
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->status)->toBe(ProjectStatus::InProgress);
});

it('maintains in_progress status when already in progress', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $project->update(['status' => ProjectStatus::InProgress]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'step' => 'step2',
        'responses' => ['question' => 'answer'],
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->status)->toBe(ProjectStatus::InProgress);
});

it('can save responses with empty values', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'step' => 'step1',
        'responses' => [
            'question1' => '',
            'question2' => null,
        ],
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->responses['step1'])->toBe([
        'question1' => null,
        'question2' => null,
    ]);
});

it('can save responses with nested arrays', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/projects/' . $project->public_id . '/responses', [
        'step' => 'step1',
        'responses' => [
            'selections' => ['option1', 'option2', 'option3'],
            'metadata' => [
                'timestamp' => '2024-01-01',
                'version' => '1.0',
            ],
        ],
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->responses['step1']['selections'])->toBe(['option1', 'option2', 'option3']);
    expect($project->responses['step1']['metadata'])->toBe([
        'timestamp' => '2024-01-01',
        'version' => '1.0',
    ]);
});
