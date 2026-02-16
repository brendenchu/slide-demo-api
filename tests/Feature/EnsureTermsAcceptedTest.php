<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('blocks access with 403 when terms not accepted', function (): void {
    $user = User::factory()->create();
    $user->terms_agreements()->delete();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects');

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'must_accept_terms' => true,
        ])
        ->assertJsonStructure([
            'terms' => ['version', 'label', 'url'],
        ]);
});

it('allows access when terms are accepted', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/projects');

    $response->assertSuccessful();
});

it('allows logout without terms acceptance', function (): void {
    $user = User::factory()->create();
    $user->terms_agreements()->delete();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertSuccessful();
});

it('allows fetching user without terms acceptance', function (): void {
    $user = User::factory()->create();
    $user->terms_agreements()->delete();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/user');

    $response->assertSuccessful();
});

it('allows fetching terms info without terms acceptance', function (): void {
    $user = User::factory()->create();
    $user->terms_agreements()->delete();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/terms');

    $response->assertSuccessful();
});

it('allows accepting terms without prior terms acceptance', function (): void {
    $user = User::factory()->create();
    $user->terms_agreements()->delete();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/terms/accept', ['accepted' => true]);

    $response->assertSuccessful();
});
