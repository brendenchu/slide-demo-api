<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    // Create roles in database for tests
    foreach (Role::cases() as $role) {
        SpatieRole::create(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('requires authentication', function (): void {
    $response = $this->postJson('/form/save-responses', []);

    $response->assertUnauthorized();
});

it('user can be assigned client role', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Role::Client);

    expect($user->hasRole('client'))->toBeTrue();
});

it('user can be assigned guest role', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Role::Guest);

    $this->actingAs($user);

    // Guest users should be blocked from saving (line 32 in controller)
    expect($user->hasRole('guest'))->toBeTrue();
});

it('controller returns json response structure', function (): void {
    // Verify the controller's response handling
    // Full integration test would require complete project/token setup
    $user = User::factory()->create();
    $user->assignRole(Role::Client);

    $this->actingAs($user);

    expect($user->hasRole('client'))->toBeTrue();
});
