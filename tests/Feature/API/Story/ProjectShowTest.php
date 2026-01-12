<?php

use App\Enums\Role;
use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    // Create roles in database for tests
    foreach (Role::cases() as $role) {
        SpatieRole::create(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('can show own project', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects/' . $project->public_id);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'title',
                'status',
                'current_step',
                'responses',
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $project->public_id,
                'title' => $project->label,
            ],
        ]);
});

it('requires authentication to show project', function (): void {
    $project = Project::factory()->create();

    $response = $this->getJson('/api/v1/projects/' . $project->public_id);

    $response->assertUnauthorized();
});

it('cannot show another users project', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects/' . $project->public_id);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'You do not have access to this project',
        ]);
});

it('admin can show any project', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin->value);
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/projects/' . $project->public_id);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $project->public_id,
            ],
        ]);
});

it('super admin can show any project', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::SuperAdmin->value);
    $otherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($superAdmin);

    $response = $this->getJson('/api/v1/projects/' . $project->public_id);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $project->public_id,
            ],
        ]);
});

it('returns 404 for non-existent project', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects/non-existent-id');

    $response->assertNotFound();
});

it('shows project with responses', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'responses' => [
            'step1' => ['question1' => 'answer1'],
            'step2' => ['question2' => 'answer2'],
        ],
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects/' . $project->public_id);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $project->public_id,
                'responses' => [
                    'step1' => ['question1' => 'answer1'],
                    'step2' => ['question2' => 'answer2'],
                ],
            ],
        ]);
});
