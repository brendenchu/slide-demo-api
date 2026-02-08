<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can delete own account with valid password', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/v1/auth/user', [
        'password' => 'password',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Account deleted successfully',
        ]);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

it('deletes user profile when account is deleted', function (): void {
    $user = User::factory()->create();
    $profileId = $user->profile->id;
    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/v1/auth/user', [
        'password' => 'password',
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseMissing('profiles', [
        'id' => $profileId,
    ]);
});

it('revokes access token when account is deleted', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token');

    $response = $this->withToken($token->plainTextToken)
        ->deleteJson('/api/v1/auth/user', [
            'password' => 'password',
        ]);

    $response->assertSuccessful();

    expect($user->tokens()->count())->toBe(0);
});

it('fails with incorrect password', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/v1/auth/user', [
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
    ]);
});

it('fails without password', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/v1/auth/user', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
    ]);
});

it('requires authentication', function (): void {
    $response = $this->deleteJson('/api/v1/auth/user', [
        'password' => 'password',
    ]);

    $response->assertUnauthorized();
});
