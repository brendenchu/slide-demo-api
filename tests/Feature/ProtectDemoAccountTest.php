<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.demo_user_email', 'demo@example.com');
});

// --- Self-delete protection ---

it('blocks demo account from deleting itself', function (): void {
    $user = User::factory()->create(['email' => 'demo@example.com']);
    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/v1/auth/user', [
        'password' => 'password',
    ]);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Demo accounts cannot be modified.',
        ]);

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

it('allows non-demo account to delete itself', function (): void {
    $user = User::factory()->create(['email' => 'regular@example.com']);
    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/v1/auth/user', [
        'password' => 'password',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

// --- Password change protection ---

it('blocks demo account from changing password', function (): void {
    $user = User::factory()->create(['email' => 'demo@example.com']);
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/password', [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Demo accounts cannot be modified.',
        ]);
});

it('allows non-demo account to change password', function (): void {
    $user = User::factory()->create(['email' => 'regular@example.com']);
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/password', [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertSuccessful();
});

// --- Demo mode off ---

it('allows demo account deletion when demo mode is off', function (): void {
    config()->set('demo.enabled', false);

    $user = User::factory()->create(['email' => 'demo@example.com']);
    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/v1/auth/user', [
        'password' => 'password',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
