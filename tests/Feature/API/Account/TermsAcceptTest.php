<?php

use App\Models\Account\Terms\Agreement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// --- Show Terms ---

it('returns terms info with accepted status false', function (): void {
    $user = User::factory()->create();
    $user->terms_agreements()->delete();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/terms');

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'version' => config('terms.current_version'),
                'accepted' => false,
            ],
        ]);
});

it('returns terms info with accepted status true', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/terms');

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'accepted' => true,
            ],
        ]);
});

// --- Accept Terms ---

it('creates an agreement record when terms are accepted', function (): void {
    $user = User::factory()->create();
    $user->terms_agreements()->delete();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/terms/accept', ['accepted' => true]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Terms accepted successfully.',
        ]);

    $this->assertDatabaseHas('account_terms_agreements', [
        'accountable_id' => $user->id,
        'accountable_type' => $user->getMorphClass(),
        'terms_version_id' => config('terms.current_version'),
    ]);

    $agreement = Agreement::query()
        ->where('accountable_id', $user->id)
        ->first();

    expect($agreement->accepted_at)->not->toBeNull();
    expect($agreement->declined_at)->toBeNull();
});

it('updates existing agreement when accepting terms again', function (): void {
    $user = User::factory()->create();
    $user->terms_agreements()->delete();

    Agreement::factory()->declined()->create([
        'accountable_id' => $user->id,
        'accountable_type' => $user->getMorphClass(),
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/terms/accept', ['accepted' => true]);

    $response->assertSuccessful();

    expect(Agreement::query()->where('accountable_id', $user->id)->count())->toBe(1);

    $agreement = Agreement::query()
        ->where('accountable_id', $user->id)
        ->first();

    expect($agreement->accepted_at)->not->toBeNull();
    expect($agreement->declined_at)->toBeNull();
});

it('validates that accepted field is required', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/terms/accept', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['accepted']);
});

it('validates that accepted field must be truthy', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/terms/accept', ['accepted' => false]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['accepted']);
});

it('requires authentication to view terms', function (): void {
    $response = $this->getJson('/api/v1/terms');

    $response->assertUnauthorized();
});

it('requires authentication to accept terms', function (): void {
    $response = $this->postJson('/api/v1/terms/accept', ['accepted' => true]);

    $response->assertUnauthorized();
});
