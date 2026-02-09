<?php

use App\Support\SafeNames;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns the safe name lists', function (): void {
    $response = $this->getJson('/api/v1/names');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'first_names',
                'last_names',
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'first_names' => SafeNames::FIRST_NAMES,
                'last_names' => SafeNames::LAST_NAMES,
            ],
        ]);
});

it('does not require authentication', function (): void {
    $response = $this->getJson('/api/v1/names');

    $response->assertSuccessful();
});
