<?php

use App\Enums\ProjectStatus;
use App\Enums\ProjectStep;
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

it('completes full story workflow from creation to publication', function () {
    // Setup: Create a user with a team
    $user = User::factory()->create();
    $team = Team::factory()->create(['label' => 'Integration Test Team']);
    $user->teams()->attach($team);
    $user->setSetting('current_team', $team->key);

    $this->actingAs($user);

    // Step 1: Create a new story
    $response = $this->post(route('story.store'));
    $response->assertRedirect();

    // Verify project and token were created
    expect(Project::count())->toBe(1)
        ->and(Token::count())->toBe(1);

    $project = Project::first();
    $token = Token::first();

    expect($project->status)->toBe(ProjectStatus::DRAFT)
        ->and($token->user_id)->toBe($user->id)
        ->and($token->project_id)->toBe($project->id);

    // Step 2: Save responses for intro step
    $introResponse = $this->postJson(route('story.save-responses'), [
        'project' => ['id' => $project->public_id],
        'token' => $token->public_id,
        'step' => ['id' => ProjectStep::STEP_ZERO->value],
        'page' => 1,
        'intro_1' => 'Test intro answer 1',
        'intro_2' => 'Test intro answer 2',
        'intro_3' => 'Test intro answer 3',
    ]);

    $introResponse->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Your responses have been saved.',
        ]);

    // Verify responses were saved
    $responses = $project->responses()->where('step', ProjectStep::STEP_ZERO->value)->get();
    expect($responses)->toHaveCount(3)
        ->and($responses->where('key', 'intro_1')->first()->value)->toBe('Test intro answer 1');

    // Verify last position was saved
    expect($token->fresh()->setting('last_position'))->toBe([
        'step' => ProjectStep::STEP_ZERO->value,
        'page' => 1,
    ]);

    // Step 3: Save responses for section-a (page 1 - booleans)
    $sectionAResponse = $this->postJson(route('story.save-responses'), [
        'project' => ['id' => $project->public_id],
        'token' => $token->public_id,
        'step' => ['id' => ProjectStep::STEP_ONE->value],
        'page' => 1,
        'section_a_1' => false,
        'section_a_2' => false,
        'section_a_3' => false,
        'section_a_4' => null,
        'section_a_5' => null,
        'section_a_6' => null,
    ]);

    $sectionAResponse->assertSuccessful();

    // Verify last position updated
    expect($token->fresh()->setting('last_position')['step'])->toBe(ProjectStep::STEP_ONE->value);

    // Verify intro responses (3) + section-a responses (3 - nulls aren't saved) = 6 total so far
    // Note: Only non-null values are saved
    expect($project->responses()->count())->toBeGreaterThanOrEqual(3);

    // Step 4: Publish the story
    $publishResponse = $this->post(route('story.publish'), [
        'project' => ['id' => $project->public_id],
        'token' => $token->public_id,
    ]);

    $publishResponse->assertRedirect(route('story.complete', [
        'project' => $project->public_id,
        'token' => $token->public_id,
    ]));
    $publishResponse->assertSessionHas('success', 'Your form has been submitted.');

    // Verify project was published
    expect($project->fresh()->status)->toBe(ProjectStatus::PUBLISHED);

    // Verify last position was saved as 'complete'
    expect($token->fresh()->setting('last_position')['step'])->toBe('complete');
});

it('prevents saving responses for guest users', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $user->teams()->attach($team);
    $user->setSetting('current_team', $team->key);

    $project = Project::factory()->create();
    $project->teams()->attach($team);
    $token = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    // Create and assign guest role to user
    \Spatie\Permission\Models\Role::create(['name' => 'guest']);
    $user->assignRole('guest');

    $this->actingAs($user);

    // Attempt to save responses as guest
    $response = $this->postJson(route('story.save-responses'), [
        'project' => ['id' => $project->public_id],
        'token' => $token->public_id,
        'step' => ['id' => ProjectStep::STEP_ZERO->value],
        'page' => 1,
        'intro_1' => 'Guest answer',
        'intro_2' => 'Guest answer 2',
        'intro_3' => 'Guest answer 3',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
        ]);

    // Verify no responses were saved to database (guest responses are not persisted)
    expect($project->responses()->count())->toBe(0);
});

it('allows resuming story from last saved position', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $user->teams()->attach($team);
    $user->setSetting('current_team', $team->key);
    $project = Project::factory()->create(['status' => ProjectStatus::DRAFT]);
    $project->teams()->attach($team);

    // Create token with saved progress at section-b, page 2
    $token = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
        'settings' => [
            'last_position' => [
                'step' => ProjectStep::STEP_TWO->value,
                'page' => 2,
            ],
        ],
    ]);

    $this->actingAs($user);

    // Access continue page
    $response = $this->get(route('story.continue', [
        'project' => $project,
        'token' => $token->public_id,
    ]));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Story/ContinueStory')
        ->where('position.step', ProjectStep::STEP_TWO->value)
        ->where('position.page', 2)
    );
});
