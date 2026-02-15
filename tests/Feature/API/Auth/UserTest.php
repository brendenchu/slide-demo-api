<?php

use App\Models\User;
use App\Support\SafeNames;
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
                    'name',
                    'email',
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
                    'name',
                    'email',
                ],
            ],
        ]);
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

    $firstName = SafeNames::FIRST_NAMES[5];
    $lastName = SafeNames::LAST_NAMES[5];

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'first_name' => $firstName,
        'last_name' => $lastName,
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => [
                    'name' => "$firstName $lastName",
                    'email' => 'user@example.com',
                ],
            ],
        ]);

    expect($user->fresh()->name)->toBe("$firstName $lastName");
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

    $firstName = SafeNames::FIRST_NAMES[10];
    $lastName = SafeNames::LAST_NAMES[10];

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => 'new@example.com',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'name' => "$firstName $lastName",
                    'email' => 'new@example.com',
                ],
            ],
        ]);

    $user->refresh();
    expect($user->name)->toBe("$firstName $lastName");
    expect($user->email)->toBe('new@example.com');
});

it('can update profile with only first name', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'user@example.com',
    ]);

    $firstName = SafeNames::FIRST_NAMES[15];

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'first_name' => $firstName,
    ]);

    $response->assertSuccessful();
    expect($user->fresh()->profile->first_name)->toBe($firstName);
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
    $firstName = SafeNames::FIRST_NAMES[20];
    $user = User::factory()->create(['email' => 'user@example.com']);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'first_name' => $firstName,
        'email' => 'user@example.com',
    ]);

    $response->assertSuccessful();
});

it('rejects first names not in the safe list when updating', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'first_name' => 'InvalidFirstName',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name']);
});

it('rejects last names not in the safe list when updating', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'last_name' => 'InvalidLastName',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_name']);
});

it('validates email max length when updating', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/user', [
        'email' => str_repeat('a', 245) . '@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('requires authentication to update profile', function (): void {
    $response = $this->putJson('/api/v1/auth/user', [
        'first_name' => SafeNames::FIRST_NAMES[0],
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

// --- Terms Acceptance Status in User Response ---

it('returns must_accept_terms true when user has not accepted terms', function (): void {
    $user = User::factory()->create();
    $user->terms_agreements()->delete();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/user');

    $response->assertSuccessful()
        ->assertJson([
            'data' => [
                'user' => [
                    'must_accept_terms' => true,
                ],
            ],
        ]);
});

it('returns must_accept_terms false when user has accepted terms', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/user');

    $response->assertSuccessful()
        ->assertJson([
            'data' => [
                'user' => [
                    'must_accept_terms' => false,
                ],
            ],
        ]);
});
