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

it('updates a user as admin', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'email',
                'role',
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ],
            'message' => 'User updated successfully',
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);
});

it('updates a user as super admin', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');
    Sanctum::actingAs($superAdmin);

    $user = User::factory()->create(['name' => 'Old Name']);

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'name' => 'New Name',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
        ]);
});

it('updates only name when only name is provided', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'original@example.com']);

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'name' => 'New Name',
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'email' => 'original@example.com',
    ]);
});

it('updates only email when only email is provided', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create(['name' => 'Original Name', 'email' => 'old@example.com']);

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'email' => 'new@example.com',
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Original Name',
        'email' => 'new@example.com',
    ]);
});

it('updates password when provided', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $response->assertSuccessful();

    $user->refresh();
    expect(password_verify('new-password123', $user->password))->toBeTrue();
});

it('updates role when provided', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create();
    $user->assignRole('client');

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'role' => 'consultant',
    ]);

    $response->assertSuccessful();

    $user->refresh();
    expect($user->hasRole('consultant'))->toBeTrue();
    expect($user->hasRole('client'))->toBeFalse();
});

it('allows updating to different roles', function (string $role): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create();
    $user->assignRole('client');

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'role' => $role,
    ]);

    $response->assertSuccessful();

    $user->refresh();
    expect($user->hasRole($role))->toBeTrue();
})->with(['client', 'consultant', 'admin', 'super-admin', 'guest']);

it('validates email format', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create();

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('validates email uniqueness except for current user', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user1 = User::factory()->create(['email' => 'user1@example.com']);
    $user2 = User::factory()->create(['email' => 'user2@example.com']);

    $response = $this->putJson("/api/v1/admin/users/{$user1->id}", [
        'email' => 'user2@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('allows user to keep their own email', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create(['email' => 'user@example.com', 'name' => 'Old Name']);

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'name' => 'New Name',
        'email' => 'user@example.com',
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'email' => 'user@example.com',
    ]);
});

it('validates password confirmation when password is provided', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create();

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'password' => 'new-password123',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('validates password minimum length when password is provided', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create();

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('validates role is valid when role is provided', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create();

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'role' => 'invalid-role',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('returns 404 for non-existent user', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->putJson('/api/v1/admin/users/99999', [
        'name' => 'New Name',
    ]);

    $response->assertNotFound();
});

it('denies access to non-admin users', function (): void {
    $user = User::factory()->create();
    $user->assignRole('client');
    Sanctum::actingAs($user);

    $otherUser = User::factory()->create();

    $response = $this->putJson("/api/v1/admin/users/{$otherUser->id}", [
        'name' => 'New Name',
    ]);

    $response->assertForbidden();
});

it('denies access to consultant users', function (): void {
    $consultant = User::factory()->create();
    $consultant->assignRole('consultant');
    Sanctum::actingAs($consultant);

    $otherUser = User::factory()->create();

    $response = $this->putJson("/api/v1/admin/users/{$otherUser->id}", [
        'name' => 'New Name',
    ]);

    $response->assertForbidden();
});

it('denies access to unauthenticated users', function (): void {
    $user = User::factory()->create();

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'name' => 'New Name',
    ]);

    $response->assertUnauthorized();
});

it('updates multiple fields at once', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
    $user->assignRole('client');

    $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
        'name' => 'New Name',
        'email' => 'new@example.com',
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
        'role' => 'consultant',
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);

    $user->refresh();
    expect($user->hasRole('consultant'))->toBeTrue();
    expect(password_verify('new-password123', $user->password))->toBeTrue();
});
