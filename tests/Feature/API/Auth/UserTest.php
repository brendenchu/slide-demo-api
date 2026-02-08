<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// Get Current User Tests
it('can get authenticated user', function (): void {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/user');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'roles',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
            ],
        ]);
});

it('includes user profile in response', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/user');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'roles',
                    'permissions',
                ],
            ],
        ]);

    expect($response['data']['user']['roles'])->toBeArray();
    expect($response['data']['user']['permissions'])->toBeArray();
});

it('requires authentication to get user', function (): void {
    $response = $this->getJson('/api/v1/auth/user');

    $response->assertUnauthorized();
});

// Update Profile Tests
it('can update authenticated user name', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'user@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'first_name' => 'New',
        'last_name' => 'Name',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => [
                    'name' => 'New Name',
                    'email' => 'user@example.com',
                ],
            ],
        ]);

    expect($user->fresh()->name)->toBe('New Name');
});

it('can update authenticated user email', function (): void {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'old@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'email' => 'new@example.com',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'new@example.com',
                ],
            ],
        ]);

    expect($user->fresh()->email)->toBe('new@example.com');
});

it('can update both name and email', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'first_name' => 'New',
        'last_name' => 'Name',
        'email' => 'new@example.com',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'name' => 'New Name',
                    'email' => 'new@example.com',
                ],
            ],
        ]);

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('new@example.com');
});

it('can update profile with only first name', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'user@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'first_name' => 'Updated',
    ]);

    $response->assertSuccessful();
    expect($user->fresh()->profile->first_name)->toBe('Updated');
    expect($user->fresh()->email)->toBe('user@example.com');
});

it('can update profile with only email', function (): void {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'old@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'email' => 'updated@example.com',
    ]);

    $response->assertSuccessful();
    expect($user->fresh()->name)->toBe('John Doe');
    expect($user->fresh()->email)->toBe('updated@example.com');
});

it('validates email format when updating', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('validates email uniqueness when updating', function (): void {
    User::factory()->create(['email' => 'existing@example.com']);
    $user = User::factory()->create(['email' => 'user@example.com']);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'email' => 'existing@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('allows user to keep their own email when updating', function (): void {
    $user = User::factory()->create(['email' => 'user@example.com']);
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'first_name' => 'Updated',
        'email' => 'user@example.com',
    ]);

    $response->assertSuccessful();
});

it('validates first_name is a string when updating', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'first_name' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name']);
});

it('validates first_name max length when updating', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'first_name' => str_repeat('a', 256),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name']);
});

it('validates last_name is a string when updating', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'last_name' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_name']);
});

it('validates last_name max length when updating', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'last_name' => str_repeat('a', 256),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_name']);
});

it('validates email max length when updating', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'email' => str_repeat('a', 245) . '@example.com', // 256 total characters
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('requires authentication to update profile', function (): void {
    $response = $this->putJson('/api/v1/auth/user', [
        'name' => 'New Name',
    ]);

    $response->assertUnauthorized();
});

it('allows empty update request', function (): void {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', []);

    $response->assertSuccessful();
    expect($user->fresh()->name)->toBe('Original Name');
    expect($user->fresh()->email)->toBe('original@example.com');
});
