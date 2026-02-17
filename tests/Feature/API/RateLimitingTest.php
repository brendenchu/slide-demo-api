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

it('enforces login rate limit at 5 per minute', function (): void {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    // Make 5 login attempts - should all be processed (not 429)
    for ($i = 1; $i <= 5; $i++) {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        expect($response->status())->not->toBe(429);
    }

    // 6th attempt should be rate limited
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
    $response->assertStatus(429);
});

it('enforces login rate limit per email to prevent credential stuffing', function (): void {
    User::factory()->create([
        'email' => 'target@example.com',
        'password' => bcrypt('password'),
    ]);

    // Make 5 failed attempts against the same email
    for ($i = 1; $i <= 5; $i++) {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'target@example.com',
            'password' => 'wrong-password',
        ]);
        expect($response->status())->not->toBe(429);
    }

    // 6th attempt against same email should be rate limited
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'target@example.com',
        'password' => 'wrong-password',
    ]);
    $response->assertStatus(429);
});

it('enforces rate limit on public endpoints', function (): void {
    // Make 30 requests - should all succeed
    for ($i = 1; $i <= 30; $i++) {
        $response = $this->getJson('/api/v1/names');
        $response->assertSuccessful();
    }

    // 31st request should be rate limited
    $response = $this->getJson('/api/v1/names');
    $response->assertStatus(429);
});

it('enforces rate limit on demo status endpoint', function (): void {
    // Make 30 requests - should all succeed
    for ($i = 1; $i <= 30; $i++) {
        $response = $this->getJson('/api/v1/demo/status');
        $response->assertSuccessful();
    }

    // 31st request should be rate limited
    $response = $this->getJson('/api/v1/demo/status');
    $response->assertStatus(429);
});
