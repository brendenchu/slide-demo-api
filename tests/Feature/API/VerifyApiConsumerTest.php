<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('allows requests from localhost IP 127.0.0.1', function (): void {
    $response = $this->getJson('/api/v1/names', [
        'REMOTE_ADDR' => '127.0.0.1',
    ]);

    $response->assertSuccessful();
});

it('allows requests with a valid Origin header', function (): void {
    config(['app.frontend_url' => 'https://myapp.example.com']);

    $response = $this->getJson('/api/v1/names', [
        'Origin' => 'https://myapp.example.com',
    ]);

    $response->assertSuccessful();
});

it('allows requests from localhost dev server origin', function (): void {
    $response = $this->getJson('/api/v1/names', [
        'Origin' => 'http://localhost:5173',
    ]);

    $response->assertSuccessful();
});

it('allows requests from alternate localhost dev server origin', function (): void {
    $response = $this->getJson('/api/v1/names', [
        'Origin' => 'http://localhost:5174',
    ]);

    $response->assertSuccessful();
});

it('allows requests with a valid Referer header', function (): void {
    config(['app.frontend_url' => 'https://myapp.example.com']);

    $response = $this->getJson('/api/v1/names', [
        'Referer' => 'https://myapp.example.com/some/page',
    ]);

    $response->assertSuccessful();
});

it('blocks requests from unauthorized origins', function (): void {
    config(['app.frontend_url' => 'https://myapp.example.com']);

    $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.50'])
        ->getJson('/api/v1/names', [
            'Origin' => 'https://evil.example.com',
        ]);

    $response->assertForbidden()
        ->assertJson([
            'message' => 'Unauthorized API consumer.',
        ]);
});

it('blocks requests with no origin from external IPs', function (): void {
    $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.50'])
        ->getJson('/api/v1/names');

    $response->assertForbidden();
});

it('blocks requests on authenticated routes from unauthorized origins', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.50'])
        ->getJson('/api/v1/auth/user', [
            'Origin' => 'https://evil.example.com',
        ]);

    $response->assertForbidden()
        ->assertJson([
            'message' => 'Unauthorized API consumer.',
        ]);
});

it('allows authenticated requests from valid origin', function (): void {
    config(['app.frontend_url' => 'https://myapp.example.com']);
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/user', [
        'Origin' => 'https://myapp.example.com',
    ]);

    $response->assertSuccessful();
});
