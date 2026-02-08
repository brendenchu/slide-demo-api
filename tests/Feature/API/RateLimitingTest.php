<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('enforces rate limit on API endpoints', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // Make 60 requests - should all succeed
    for ($i = 1; $i <= 60; $i++) {
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertSuccessful();
    }

    // 61st request should be rate limited
    $response = $this->getJson('/api/v1/auth/user');
    $response->assertStatus(429)
        ->assertJsonStructure([
            'message',
        ]);
});

it('rate limit applies per user', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // User 1 makes 60 requests
    Sanctum::actingAs($user1);
    for ($i = 1; $i <= 60; $i++) {
        $this->getJson('/api/v1/auth/user')->assertSuccessful();
    }

    // User 2 can still make requests (different rate limit)
    Sanctum::actingAs($user2);
    $response = $this->getJson('/api/v1/auth/user');
    $response->assertSuccessful();

    // But user 1's 61st request is limited
    Sanctum::actingAs($user1);
    $response = $this->getJson('/api/v1/auth/user');
    $response->assertStatus(429);
});

it('includes rate limit headers in responses', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/user');

    $response->assertSuccessful()
        ->assertHeader('X-RateLimit-Limit')
        ->assertHeader('X-RateLimit-Remaining');
});

it('rate limit headers decrease with each request', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // First request
    $response1 = $this->getJson('/api/v1/auth/user');
    $remaining1 = $response1->headers->get('X-RateLimit-Remaining');

    // Second request
    $response2 = $this->getJson('/api/v1/auth/user');
    $remaining2 = $response2->headers->get('X-RateLimit-Remaining');

    // Remaining should decrease
    expect((int) $remaining2)->toBeLessThan((int) $remaining1);
});
