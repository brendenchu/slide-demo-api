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

it('can update own project', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'title' => 'Updated Title',
        'description' => 'Updated Description',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Project updated successfully',
            'data' => [
                'id' => $project->public_id,
                'title' => 'Updated Title',
                'description' => 'Updated Description',
            ],
        ]);

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'label' => 'Updated Title',
        'description' => 'Updated Description',
    ]);
});

it('requires authentication to update project', function (): void {
    $project = Project::factory()->create();

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'title' => 'Updated Title',
    ]);

    $response->assertUnauthorized();
});

it('cannot update another users project', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'title' => 'Hacked Title',
    ]);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'You do not have access to this project',
        ]);
});

it('admin can update any project', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin->value);
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($admin);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'title' => 'Admin Updated',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'data' => [
                'title' => 'Admin Updated',
            ],
        ]);
});

it('super admin can update any project', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::SuperAdmin->value);
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($superAdmin);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'title' => 'Super Admin Updated',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'data' => [
                'title' => 'Super Admin Updated',
            ],
        ]);
});

it('returns 404 for non-existent project', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/non-existent-id', [
        'title' => 'Updated',
    ]);

    $response->assertNotFound();
});

it('can update only title', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'description' => 'Original Description',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'title' => 'New Title Only',
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->label)->toBe('New Title Only');
    expect($project->description)->toBe('Original Description');
});

it('can update only description', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'label' => 'Original Title',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'description' => 'New Description Only',
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->label)->toBe('Original Title');
    expect($project->description)->toBe('New Description Only');
});

it('can update project status to draft', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $project->update(['status' => ProjectStatus::InProgress]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'status' => 'draft',
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->status)->toBe(ProjectStatus::Draft);
});

it('can update project status to in_progress', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'status' => ProjectStatus::Draft,
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'status' => 'in_progress',
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->status)->toBe(ProjectStatus::InProgress);
});

it('can update project status to completed', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'status' => 'completed',
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->status)->toBe(ProjectStatus::Completed);
});

it('can update current step', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'current_step' => 'intro',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'current_step' => 'step2',
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->current_step)->toBe('step2');
});

it('can set description to null', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'description' => 'Original Description',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'description' => null,
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->description)->toBeNull();
});

it('validates title must be string', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'title' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

it('validates title max length', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'title' => str_repeat('a', 256),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

it('validates description must be string when provided', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'description' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['description']);
});

it('validates status must be valid value', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'status' => 'not-a-valid-status',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('can update multiple fields at once', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $project->update(['current_step' => 'intro']);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/projects/' . $project->public_id, [
        'title' => 'Multi Update',
        'description' => 'Multi Description',
        'status' => 'in_progress',
        'current_step' => 'step3',
    ]);

    $response->assertSuccessful();

    $project->refresh();
    expect($project->label)->toBe('Multi Update');
    expect($project->description)->toBe('Multi Description');
    expect($project->status)->toBe(ProjectStatus::InProgress);
    expect($project->current_step)->toBe('step3');
});
