<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    // Create roles in database for tests
    foreach (Role::cases() as $role) {
        SpatieRole::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('shows a user for admin', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    $user->assignRole('client');

    $response = $this->getJson("/api/v1/admin/users/{$user->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'email',
                'role',
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => (string) $user->id,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'client',
            ],
        ]);
});

it('shows a user for super admin', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');
    Sanctum::actingAs($superAdmin);

    $user = User::factory()->create();

    $response = $this->getJson("/api/v1/admin/users/{$user->id}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
        ]);
});

it('includes user role in response', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create();
    $user->assignRole('consultant');

    $response = $this->getJson("/api/v1/admin/users/{$user->id}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'role' => 'consultant',
            ],
        ]);
});

it('returns 404 for non-existent user', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/admin/users/99999');

    $response->assertNotFound();
});

it('denies access to non-admin users', function (): void {
    $user = User::factory()->create();
    $user->assignRole('client');
    Sanctum::actingAs($user);

    $otherUser = User::factory()->create();

    $response = $this->getJson("/api/v1/admin/users/{$otherUser->id}");

    $response->assertForbidden();
});

it('denies access to consultant users', function (): void {
    $consultant = User::factory()->create();
    $consultant->assignRole('consultant');
    Sanctum::actingAs($consultant);

    $otherUser = User::factory()->create();

    $response = $this->getJson("/api/v1/admin/users/{$otherUser->id}");

    $response->assertForbidden();
});

it('denies access to guest users', function (): void {
    $guest = User::factory()->create();
    $guest->assignRole('guest');
    Sanctum::actingAs($guest);

    $otherUser = User::factory()->create();

    $response = $this->getJson("/api/v1/admin/users/{$otherUser->id}");

    $response->assertForbidden();
});

it('denies access to unauthenticated users', function (): void {
    $user = User::factory()->create();

    $response = $this->getJson("/api/v1/admin/users/{$user->id}");

    $response->assertUnauthorized();
});
