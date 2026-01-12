<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can logout with valid token', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)
        ->postJson('/api/v1/auth/logout');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'message',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Logout successful',
        ]);
});

it('deletes the current access token on logout', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token');

    expect($user->tokens()->count())->toBe(1);

    $this->withToken($token->plainTextToken)
        ->postJson('/api/v1/auth/logout')
        ->assertSuccessful();

    expect($user->tokens()->count())->toBe(0);
});

it('only deletes the current token when multiple tokens exist', function (): void {
    $user = User::factory()->create();
    $token1 = $user->createToken('token-1');
    $token2 = $user->createToken('token-2');

    expect($user->tokens()->count())->toBe(2);

    $this->withToken($token1->plainTextToken)
        ->postJson('/api/v1/auth/logout')
        ->assertSuccessful();

    expect($user->tokens()->count())->toBe(1);
    expect($user->tokens()->first()->name)->toBe('token-2');
});

it('requires authentication', function (): void {
    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertUnauthorized();
});

it('cannot logout with invalid token', function (): void {
    $response = $this->withToken('invalid-token')
        ->postJson('/api/v1/auth/logout');

    $response->assertUnauthorized();
});

it('cannot logout after token is deleted', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token');

    // Delete the token
    $token->accessToken->delete();

    // Try to use the deleted token
    $response = $this->withToken($token->plainTextToken)
        ->postJson('/api/v1/auth/logout');

    $response->assertUnauthorized();
});
