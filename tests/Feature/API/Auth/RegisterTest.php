<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    foreach (Role::cases() as $role) {
        SpatieRole::create(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('can register a new user with valid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'roles',
                ],
                'token',
            ],
            'message',
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

    expect($response['data']['token'])->not->toBeNull()->toBeString();
    expect(User::where('email', 'john@example.com')->exists())->toBeTrue();
});

it('assigns client role to newly registered users', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'jane@example.com')->first();
    expect($user->hasRole('client'))->toBeTrue();
});

it('creates a profile for newly registered users', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Bob Smith',
        'email' => 'bob@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'bob@example.com')->first();
    expect($user->profile)->not->toBeNull();
});

it('validates required fields', function () {
    $response = $this->postJson('/api/v1/auth/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('validates email format', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'not-an-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('validates email uniqueness', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('validates password confirmation', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'differentpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('validates password minimum length', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});
