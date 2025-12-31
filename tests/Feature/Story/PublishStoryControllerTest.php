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

it('publishes a project successfully', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create(['status' => ProjectStatus::DRAFT]);
    $project->teams()->attach($team);
    $token = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('story.publish'), [
        'project' => ['id' => $project->public_id],
        'token' => $token->public_id,
    ]);

    $response->assertRedirect(route('story.complete', [
        'project' => $project->public_id,
        'token' => $token->public_id,
    ]));
    $response->assertSessionHas('success', 'Your form has been submitted.');

    expect($project->fresh()->status)->toBe(ProjectStatus::PUBLISHED);
});

it('saves last position to complete after publishing', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create(['status' => ProjectStatus::DRAFT]);
    $project->teams()->attach($team);
    $token = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user);

    $this->post(route('story.publish'), [
        'project' => ['id' => $project->public_id],
        'token' => $token->public_id,
    ]);

    expect($token->fresh()->setting('last_position')['step'])->toBe('complete');
});

it('redirects to complete page if project is already published', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();
    $project->teams()->attach($team);
    $project->status = ProjectStatus::PUBLISHED;
    $project->save();

    $token = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('story.publish'), [
        'project' => ['id' => $project->public_id],
        'token' => $token->public_id,
    ]);

    $response->assertRedirect(route('story.complete', [
        'project' => $project->public_id,
        'token' => $token->public_id,
    ]));
    $response->assertSessionHas('info', 'This project has already been submitted.');
});

it('redirects to create page when token is invalid', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create(['status' => ProjectStatus::DRAFT]);
    $project->teams()->attach($team);

    // Create token for different user
    $otherUser = User::factory()->create();
    $token = Token::factory()->create([
        'user_id' => $otherUser->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('story.publish'), [
        'project' => ['id' => $project->public_id],
        'token' => $token->public_id,
    ]);

    $response->assertRedirect(route('story.create'));
    $response->assertSessionHas('error', 'User token is invalid.');
});

it('redirects to create page when token is revoked', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create(['status' => ProjectStatus::DRAFT]);
    $project->teams()->attach($team);
    $token = Token::factory()->revoked()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('story.publish'), [
        'project' => ['id' => $project->public_id],
        'token' => $token->public_id,
    ]);

    $response->assertRedirect(route('story.create'));
    $response->assertSessionHas('error', 'User token is invalid.');
});
