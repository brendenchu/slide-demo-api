<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.super_admin_email', 'admin@demo.com');
    config()->set('demo.client_email', 'client@demo.com');

    foreach (Role::cases() as $role) {
        SpatieRole::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }
});

// --- Self-delete protection ---

it('blocks demo account from deleting itself', function (): void {
    $user = User::factory()->create(['email' => 'admin@demo.com']);
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
    $user = User::factory()->create(['email' => 'client@demo.com']);
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

// --- Admin delete protection ---

it('blocks admin from deleting a demo account', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SuperAdmin->value);
    Sanctum::actingAs($admin);

    $demoUser = User::factory()->create(['email' => 'admin@demo.com']);

    $response = $this->deleteJson("/api/v1/admin/users/{$demoUser->id}");

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Demo accounts cannot be modified.',
        ]);

    $this->assertDatabaseHas('users', ['id' => $demoUser->id]);
});

// --- Admin update protection ---

it('blocks admin from updating a demo account', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SuperAdmin->value);
    Sanctum::actingAs($admin);

    $demoUser = User::factory()->create(['email' => 'admin@demo.com']);

    $response = $this->putJson("/api/v1/admin/users/{$demoUser->id}", [
        'name' => 'Hacked Name',
        'email' => 'hacked@example.com',
    ]);

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Demo accounts cannot be modified.',
        ]);
});

// --- Demo mode off ---

it('allows demo account deletion when demo mode is off', function (): void {
    config()->set('demo.enabled', false);

    $user = User::factory()->create(['email' => 'admin@demo.com']);
    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/v1/auth/user', [
        'password' => 'password',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
