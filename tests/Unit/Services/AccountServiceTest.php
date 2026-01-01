<?php

use App\Models\Account\Profile;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = new AccountService;
});

it('gets user by email', function (): void {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $result = $this->service->getUserByEmail('test@example.com');

    expect($result)->toBeInstanceOf(User::class)
        ->and($result->id)->toBe($user->id);
});

it('gets user by slug when profile exists', function (): void {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    $result = $this->service->getUserBySlug($profile->public_id);

    expect($result)->toBeInstanceOf(User::class)
        ->and($result->id)->toBe($user->id);
});

it('throws exception when profile not found by slug', function (): void {
    $this->service->getUserBySlug('non-existent-slug');
})->throws(Exception::class, 'Profile not found for slug: non-existent-slug');

it('generates a password with correct length', function (): void {
    $password = $this->service->generatePassword();

    expect($password)->toBeString()
        ->and(strlen((string) $password))->toBe(16);
});

it('sets user by User instance', function (): void {
    $user = User::factory()->create();

    $result = $this->service->setUser($user);

    expect($result)->toBeInstanceOf(AccountService::class)
        ->and($this->service->getUser()->id)->toBe($user->id);
});

it('sets user by email string', function (): void {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $result = $this->service->setUser('test@example.com');

    expect($result)->toBeInstanceOf(AccountService::class)
        ->and($this->service->getUser()->id)->toBe($user->id);
});

it('sets user by slug string', function (): void {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    $result = $this->service->setUser($profile->public_id);

    expect($result)->toBeInstanceOf(AccountService::class)
        ->and($this->service->getUser()->id)->toBe($user->id);
});

it('throws exception when getting user without setting it first', function (): void {
    $this->service->getUser();
})->throws(Exception::class, 'No user set.');
