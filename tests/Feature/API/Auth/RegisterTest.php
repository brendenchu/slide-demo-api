<?php

use App\Models\User;
use App\Support\SafeNames;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can register a new user with valid data', function (): void {
    $firstName = SafeNames::FIRST_NAMES[0];
    $lastName = SafeNames::LAST_NAMES[0];

    $response = $this->postJson('/api/v1/auth/register', [
        'first_name' => $firstName,
        'last_name' => $lastName,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'data' => [
                'user' => [
                    'name',
                    'email',
                ],
                'token',
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'name' => "$firstName $lastName",
                ],
            ],
        ]);

    expect($response['data']['token'])->not->toBeNull()->toBeString();

    // Email should be auto-generated
    $email = $response['data']['user']['email'];
    expect($email)->toContain(strtolower($firstName));
    expect($email)->toContain(strtolower($lastName));
    expect($email)->toEndWith('@example.com');
    expect(User::where('email', $email)->exists())->toBeTrue();
});

it('creates a profile for newly registered users with names', function (): void {
    $firstName = SafeNames::FIRST_NAMES[1];
    $lastName = SafeNames::LAST_NAMES[1];

    $response = $this->postJson('/api/v1/auth/register', [
        'first_name' => $firstName,
        'last_name' => $lastName,
    ]);

    $email = $response['data']['user']['email'];
    $user = User::where('email', $email)->first();
    expect($user->profile)->not->toBeNull();
    expect($user->profile->first_name)->toBe($firstName);
    expect($user->profile->last_name)->toBe($lastName);
});

it('generates unique emails for same name combinations', function (): void {
    $firstName = SafeNames::FIRST_NAMES[0];
    $lastName = SafeNames::LAST_NAMES[0];

    $response1 = $this->postJson('/api/v1/auth/register', [
        'first_name' => $firstName,
        'last_name' => $lastName,
    ]);

    $response2 = $this->postJson('/api/v1/auth/register', [
        'first_name' => $firstName,
        'last_name' => $lastName,
    ]);

    $response1->assertStatus(201);
    $response2->assertStatus(201);

    $email1 = $response1['data']['user']['email'];
    $email2 = $response2['data']['user']['email'];
    expect($email1)->not->toBe($email2);
});

it('sets password to default value', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'first_name' => SafeNames::FIRST_NAMES[0],
        'last_name' => SafeNames::LAST_NAMES[0],
    ]);

    $response->assertStatus(201);

    $email = $response['data']['user']['email'];
    $user = User::where('email', $email)->first();
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

it('validates required fields', function (): void {
    $response = $this->postJson('/api/v1/auth/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name', 'last_name']);
});

it('rejects first names not in the safe list', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'first_name' => 'InvalidFirstName',
        'last_name' => SafeNames::LAST_NAMES[0],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name']);
});

it('rejects last names not in the safe list', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'first_name' => SafeNames::FIRST_NAMES[0],
        'last_name' => 'InvalidLastName',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_name']);
});
