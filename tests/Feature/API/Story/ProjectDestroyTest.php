<?php

use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can delete own project', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/v1/projects/' . $project->public_id);

    $response->assertNoContent();

    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);
});

it('requires authentication to delete project', function (): void {
    $project = Project::factory()->create();

    $response = $this->deleteJson('/api/v1/projects/' . $project->public_id);

    $response->assertUnauthorized();
});

it('cannot delete another users project', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/v1/projects/' . $project->public_id);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'You do not have access to this project',
        ]);

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
    ]);
});

it('returns 404 for non-existent project', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/v1/projects/non-existent-id');

    $response->assertNotFound();
});

it('can delete project with responses', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'responses' => [
            'step1' => ['question1' => 'answer1'],
        ],
    ]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/v1/projects/' . $project->public_id);

    $response->assertNoContent();

    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);
});
