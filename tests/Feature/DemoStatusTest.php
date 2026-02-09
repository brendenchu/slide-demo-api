<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns demo mode enabled with limits', function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.limits', [
        'max_users' => 25,
        'max_teams_per_user' => 3,
        'max_projects_per_team' => 5,
        'max_invitations_per_team' => 5,
    ]);

    $response = $this->getJson('/api/v1/demo/status');

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'demo_mode' => true,
                'limits' => [
                    'max_users' => 25,
                    'max_teams_per_user' => 3,
                    'max_projects_per_team' => 5,
                    'max_invitations_per_team' => 5,
                ],
            ],
        ]);
});

it('returns demo mode disabled with null limits', function (): void {
    config()->set('demo.enabled', false);

    $response = $this->getJson('/api/v1/demo/status');

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'demo_mode' => false,
                'limits' => null,
            ],
        ]);
});

it('is accessible without authentication', function (): void {
    config()->set('demo.enabled', true);

    $response = $this->getJson('/api/v1/demo/status');

    $response->assertSuccessful();
});
