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
        SpatieRole::create(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('deletes a user as admin', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/users/{$user->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

it('deletes a user as super admin', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');
    Sanctum::actingAs($superAdmin);

    $user = User::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/users/{$user->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

it('deletes user profile when user is deleted', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create();
    $profileId = $user->profile->id;

    $response = $this->deleteJson("/api/v1/admin/users/{$user->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('profiles', [
        'id' => $profileId,
    ]);
});

it('prevents admin from deleting themselves', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/v1/admin/users/{$admin->id}");

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'You cannot delete your own account',
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});

it('prevents super admin from deleting themselves', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');
    Sanctum::actingAs($superAdmin);

    $response = $this->deleteJson("/api/v1/admin/users/{$superAdmin->id}");

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'You cannot delete your own account',
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $superAdmin->id,
    ]);
});

it('returns 404 for non-existent user', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->deleteJson('/api/v1/admin/users/99999');

    $response->assertNotFound();
});

it('denies access to non-admin users', function (): void {
    $user = User::factory()->create();
    $user->assignRole('client');
    Sanctum::actingAs($user);

    $otherUser = User::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/users/{$otherUser->id}");

    $response->assertForbidden();
});

it('denies access to consultant users', function (): void {
    $consultant = User::factory()->create();
    $consultant->assignRole('consultant');
    Sanctum::actingAs($consultant);

    $otherUser = User::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/users/{$otherUser->id}");

    $response->assertForbidden();
});

it('denies access to guest users', function (): void {
    $guest = User::factory()->create();
    $guest->assignRole('guest');
    Sanctum::actingAs($guest);

    $otherUser = User::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/users/{$otherUser->id}");

    $response->assertForbidden();
});

it('denies access to unauthenticated users', function (): void {
    $user = User::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/users/{$user->id}");

    $response->assertUnauthorized();
});

it('can delete users with different roles', function (string $role): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create();
    $user->assignRole($role);

    $response = $this->deleteJson("/api/v1/admin/users/{$user->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
})->with(['client', 'consultant', 'admin', 'super-admin', 'guest']);
