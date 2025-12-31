<?php

use App\Enums\ProjectStatus;
use App\Http\Middleware\Account\EnsureTermsAreAccepted;
use App\Models\Account\Team;
use App\Models\Story\Project;
use App\Models\Story\Token;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(EnsureTermsAreAccepted::class);
});

it('displays new story page when no draft exists', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('story.create'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Story/NewStory')
    );
});

it('redirects to continue page when draft token exists', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create(['status' => ProjectStatus::DRAFT]);
    $project->teams()->attach($team);

    $token = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('story.create'));

    $response->assertRedirect(route('story.continue', [
        'project' => $project->public_id,
        'token' => $token->public_id,
    ]));
});

it('bypasses expiration and revocation when checking for draft token', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create(['status' => ProjectStatus::DRAFT]);
    $project->teams()->attach($team);

    // Create expired and revoked token
    $token = Token::factory()->expired()->revoked()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('story.create'));

    // Should still redirect to continue page
    $response->assertRedirect(route('story.continue', [
        'project' => $project->public_id,
        'token' => $token->public_id,
    ]));
});

it('creates new project and token when storing', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $user->teams()->attach($team);
    $user->setSetting('current_team', $team->key);

    $this->actingAs($user);

    $response = $this->post(route('story.store'));

    expect(Project::count())->toBe(1)
        ->and(Token::count())->toBe(1);

    $project = Project::first();
    $token = Token::first();

    expect($project->teams->first()->id)->toBe($team->id)
        ->and($token->user_id)->toBe($user->id)
        ->and($token->project_id)->toBe($project->id);

    $response->assertRedirect(route('story.form', [
        'project' => $project->public_id,
        'step' => 'intro',
        'token' => $token->public_id,
    ]));
});

it('creates project with auto-generated name and description', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['label' => 'Test Team']);
    $user->teams()->attach($team);
    $user->setSetting('current_team', $team->key);

    $this->actingAs($user);

    $this->post(route('story.store'));

    $project = Project::first();

    expect($project->label)->toContain('My Project')
        ->and($project->description)->toContain('Test Team');
});

it('requires authentication to create new story', function () {
    $response = $this->get(route('story.create'));

    $response->assertRedirect(route('login'));
});

it('requires authentication to store new story', function () {
    $response = $this->post(route('story.store'));

    $response->assertRedirect(route('login'));
});
