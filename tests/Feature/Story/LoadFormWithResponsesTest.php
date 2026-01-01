<?php

use App\Enums\Story\ProjectStatus;
use App\Enums\Story\ProjectStep;
use App\Http\Middleware\Account\EnsureTermsAreAccepted;
use App\Models\Account\Team;
use App\Models\Story\Project;
use App\Models\Story\Token;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutMiddleware(EnsureTermsAreAccepted::class);
});

it('loads form with previously saved responses for prepopulation', function (): void {
    // Setup: Create user with team
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $user->teams()->attach($team);
    $user->setSetting('current_team', $team->key);

    // Create a draft project with saved responses
    $project = Project::factory()->create(['status' => ProjectStatus::DRAFT]);
    $project->teams()->attach($team);

    // Save some responses for intro step
    $project->responses()->create([
        'step' => ProjectStep::STEP_ZERO->value,
        'key' => 'intro_1',
        'value' => 'My saved answer 1',
    ]);

    $project->responses()->create([
        'step' => ProjectStep::STEP_ZERO->value,
        'key' => 'intro_2',
        'value' => 'My saved answer 2',
    ]);

    $project->responses()->create([
        'step' => ProjectStep::STEP_ZERO->value,
        'key' => 'intro_3',
        'value' => 'My saved answer 3',
    ]);

    // Create token with last position saved at intro step, page 1
    $token = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
        'settings' => [
            'last_position' => [
                'step' => ProjectStep::STEP_ZERO->value,
                'page' => 1,
            ],
        ],
    ]);

    $this->actingAs($user);

    // Step 1: Access continue page - should show saved position
    $continueResponse = $this->get(route('story.continue', [
        'project' => $project,
    ]));

    $continueResponse->assertSuccessful();
    $continueResponse->assertInertia(fn ($page) => $page
        ->component('Story/ContinueStory')
        ->where('position.step', ProjectStep::STEP_ZERO->value)
        ->where('position.page', 1)
        ->where('token', $token->public_id)
    );

    // Step 2: Load the form at the saved position - should include previous responses
    $formResponse = $this->get(route('story.form', [
        'project' => $project,
        'step' => ProjectStep::STEP_ZERO->slug(),
        'token' => $token->public_id,
        'page' => 1,
    ]));

    $formResponse->assertSuccessful();
    $formResponse->assertInertia(fn ($page) => $page
        ->component('Story/StoryForm')
        ->where('step.id', ProjectStep::STEP_ZERO->value)
        ->where('page', 1)
        ->where('token', $token->public_id)
        // Verify that saved responses are passed to the frontend
        ->where('story.intro_1', 'My saved answer 1')
        ->where('story.intro_2', 'My saved answer 2')
        ->where('story.intro_3', 'My saved answer 3')
    );

    // Verify the responses exist in database
    expect($project->responses()->count())->toBe(3)
        ->and($project->responses()->where('key', 'intro_1')->first()->value)->toBe('My saved answer 1')
        ->and($project->responses()->where('key', 'intro_2')->first()->value)->toBe('My saved answer 2')
        ->and($project->responses()->where('key', 'intro_3')->first()->value)->toBe('My saved answer 3');
});

it('loads form with last position after saving responses in middle of workflow', function (): void {
    // Setup
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $user->teams()->attach($team);
    $user->setSetting('current_team', $team->key);

    $project = Project::factory()->create(['status' => ProjectStatus::DRAFT]);
    $project->teams()->attach($team);

    $token = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user);

    // Save responses for section-a, page 1
    $this->post(route('story.save-responses', ['project' => $project]), [
        'token' => $token->public_id,
        'step' => ['id' => ProjectStep::STEP_ONE->value],
        'page' => 1,
        'section_a_4' => 'Answer 4',
        'section_a_5' => 'Answer 5',
    ]);

    // Reload token to get fresh settings
    $token->refresh();

    // Verify last position was saved correctly
    expect($token->setting('last_position'))->toBe([
        'step' => ProjectStep::STEP_ONE->value,
        'page' => 1,
    ]);

    // Access continue page - should show last saved position
    $continueResponse = $this->get(route('story.continue', [
        'project' => $project,
    ]));

    $continueResponse->assertSuccessful();
    $continueResponse->assertInertia(fn ($page) => $page
        ->component('Story/ContinueStory')
        ->where('position.step', ProjectStep::STEP_ONE->value)
        ->where('position.page', 1)
    );

    // Load form at saved position - should include saved responses
    $formResponse = $this->get(route('story.form', [
        'project' => $project,
        'step' => ProjectStep::STEP_ONE->slug(),
        'token' => $token->public_id,
        'page' => 1,
    ]));

    $formResponse->assertSuccessful();
    $formResponse->assertInertia(fn ($page) => $page
        ->component('Story/StoryForm')
        ->where('story.section_a_4', 'Answer 4')
        ->where('story.section_a_5', 'Answer 5')
    );
});
