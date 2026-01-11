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

it('creates a new user as admin', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'client',
    ]);

    $response->assertCreated()
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
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'client',
            ],
            'message' => 'User created successfully',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);
});

it('creates a new user as super admin', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');
    Sanctum::actingAs($superAdmin);

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'consultant',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
    ]);
});

it('assigns the specified role to the new user', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'Consultant User',
        'email' => 'consultant@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'consultant',
    ]);

    $response->assertCreated();

    $user = User::where('email', 'consultant@example.com')->first();
    expect($user->hasRole('consultant'))->toBeTrue();
});

it('creates profile automatically for new user', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'client',
    ]);

    $response->assertCreated();

    $user = User::where('email', 'john@example.com')->first();
    expect($user->profile)->not->toBeNull();
    expect($user->profile->first_name)->toBe('John');
    expect($user->profile->last_name)->toBe('Doe');
});

it('validates required fields', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/admin/users', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
});

it('validates email format', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'John Doe',
        'email' => 'not-an-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'client',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('validates email uniqueness', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'John Doe',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'client',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('validates password confirmation', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different-password',
        'role' => 'client',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('validates password minimum length', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
        'role' => 'client',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('validates role is valid', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'invalid-role',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('can create user with different roles', function (string $role): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => "User {$role}",
        'email' => "{$role}@example.com",
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => $role,
    ]);

    $response->assertCreated();

    $user = User::where('email', "{$role}@example.com")->first();
    expect($user->hasRole($role))->toBeTrue();
})->with(['client', 'consultant', 'admin', 'super-admin', 'guest']);

it('denies access to non-admin users', function (): void {
    $user = User::factory()->create();
    $user->assignRole('client');
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'client',
    ]);

    $response->assertForbidden();
});

it('denies access to consultant users', function (): void {
    $consultant = User::factory()->create();
    $consultant->assignRole('consultant');
    Sanctum::actingAs($consultant);

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'client',
    ]);

    $response->assertForbidden();
});

it('denies access to unauthenticated users', function (): void {
    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'client',
    ]);

    $response->assertUnauthorized();
});
