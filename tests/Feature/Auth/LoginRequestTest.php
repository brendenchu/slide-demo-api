<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    // Create roles
    foreach (Role::cases() as $role) {
        SpatieRole::create(['name' => $role->value, 'guard_name' => 'web']);
    }

    // Create guest user
    $this->guestUser = User::factory()->create([
        'name' => config('demo.guest_name'),
        'email' => config('demo.guest_email'),
        'password' => bcrypt(config('demo.guest_password')),
    ]);
    $this->guestUser->assignRole(Role::Guest);
});

it('allows login with guest alias', function (): void {
    $response = $this->post('/login', [
        'email' => 'guest',
        'password' => 'guest',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($this->guestUser);
});

it('allows login with full guest email', function (): void {
    $response = $this->post('/login', [
        'email' => 'guest@example.com',
        'password' => 'guest',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($this->guestUser);
});

it('handles case-insensitive guest alias', function (string $alias): void {
    $response = $this->post('/login', [
        'email' => $alias,
        'password' => 'guest',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($this->guestUser);
})->with([
    'lowercase' => 'guest',
    'uppercase' => 'GUEST',
    'titlecase' => 'Guest',
    'mixedcase' => 'gUeSt',
]);

it('handles guest alias with whitespace', function (): void {
    $response = $this->post('/login', [
        'email' => '  guest  ',
        'password' => 'guest',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($this->guestUser);
});

it('rejects guest login with wrong password', function (): void {
    $response = $this->post('/login', [
        'email' => 'guest',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('applies rate limiting to guest alias', function (): void {
    RateLimiter::clear('guest@example.com|127.0.0.1');

    // Make 5 failed attempts (the limit)
    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => 'guest',
            'password' => 'wrong-password',
        ]);
    }

    // 6th attempt should be throttled
    $response = $this->post('/login', [
        'email' => 'guest',
        'password' => 'guest',
    ]);

    $response->assertSessionHasErrors('email');
    expect($response->exception->errors()['email'][0])
        ->toContain('Too many login attempts');
});

it('allows normal email login for non-guest users', function (): void {
    $user = User::factory()->create([
        'email' => 'regular@example.com',
        'password' => bcrypt('password'),
    ]);
    $user->assignRole(Role::Client);

    $response = $this->post('/login', [
        'email' => 'regular@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials for non-guest users', function (): void {
    $user = User::factory()->create([
        'email' => 'regular@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => 'regular@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
