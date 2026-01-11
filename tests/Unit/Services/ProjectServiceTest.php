<?php

use App\Enums\Story\ProjectStatus;
use App\Enums\Story\ProjectStep;
use App\Models\Account\Team;
use App\Models\Story\Project;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    Log::spy();
});

it('sets project by instance', function (): void {
    $project = Project::factory()->create();
    $service = new ProjectService;

    $result = $service->setProject($project);

    expect($result)->toBeInstanceOf(ProjectService::class)
        ->and($service->getProject())->toBeInstanceOf(Project::class)
        ->and($service->getProject()->id)->toBe($project->id);
});

it('sets project by public_id string', function (): void {
    $project = Project::factory()->create();
    $service = new ProjectService;

    $result = $service->setProject($project->public_id);

    expect($result)->toBeInstanceOf(ProjectService::class)
        ->and($service->getProject()->id)->toBe($project->id);
});

it('gets project when set', function (): void {
    $project = Project::factory()->create();
    $service = new ProjectService;
    $service->setProject($project);

    $result = $service->getProject();

    expect($result)->toBeInstanceOf(Project::class)
        ->and($result->id)->toBe($project->id);
});

it('throws exception when getting project without setting it', function (): void {
    $service = new ProjectService;

    expect(fn (): Project => $service->getProject())->toThrow(Exception::class, 'No project set.');
});

it('sets steps with ProjectStep enum', function (): void {
    $service = new ProjectService;

    $result = $service->setSteps(ProjectStep::Intro);

    expect($result)->toBeInstanceOf(ProjectService::class);
});

it('sets steps with string value', function (): void {
    $service = new ProjectService;

    $result = $service->setSteps('intro');

    expect($result)->toBeInstanceOf(ProjectService::class);
});

it('sets multiple steps with variadic arguments', function (): void {
    $service = new ProjectService;

    $result = $service->setSteps(ProjectStep::Intro, 'section-a', ProjectStep::SectionB);

    expect($result)->toBeInstanceOf(ProjectService::class);
});

it('creates project with validated data', function (): void {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    $team = Team::factory()->create();
    $service = new ProjectService;

    $validated = [
        'name' => 'Test Project',
        'description' => 'Test Description',
    ];

    $project = $service->createProject($team, $validated);

    expect($project)->toBeInstanceOf(Project::class)
        ->and($project->label)->toBe('Test Project')
        ->and($project->description)->toBe('Test Description')
        ->and($project->key)->toContain('test-project')
        ->and($project->teams->first()->id)->toBe($team->id);

    Log::shouldHaveReceived('info')->with('Project created successfully', \Mockery::on(fn ($arg): bool => isset($arg['project_id']) && isset($arg['team_id'])));
});

it('creates project without validated data using auto-generated values', function (): void {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    $team = Team::factory()->create();
    $service = new ProjectService;

    $project = $service->createProject($team);

    expect($project)->toBeInstanceOf(Project::class)
        ->and($project->label)->toContain('My Project')
        ->and($project->description)->toContain($team->label);
});

it('saves responses and creates new records', function (): void {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    $team = Team::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $project->teams()->attach($team);
    $service = new ProjectService;

    $service->setProject($project)->setSteps(ProjectStep::Intro);

    $responses = [
        'intro_1' => 'Value 1',
        'intro_2' => 'Value 2',
        'intro_3' => 'Value 3',
    ];

    $service->saveResponses($responses);

    expect($project->responses()->get())->toHaveCount(3)
        ->and($project->responses()->where('key', 'intro_1')->first()->value)->toBe('Value 1')
        ->and($project->responses()->where('key', 'intro_2')->first()->value)->toBe('Value 2');

    Log::shouldHaveReceived('info')->with('Project responses saved', \Mockery::on(fn ($arg): bool => $arg['response_count'] === 3));
});

it('saves responses and updates existing records', function (): void {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    $team = Team::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $project->teams()->attach($team);
    $service = new ProjectService;

    $service->setProject($project)->setSteps(ProjectStep::Intro);

    // Create initial responses
    $project->responses()->create([
        'step' => ProjectStep::Intro->value,
        'key' => 'intro_1',
        'value' => 'Old Value',
    ]);

    // Update with new value
    $service->saveResponses(['intro_1' => 'New Value']);

    expect($project->responses()->where('key', 'intro_1')->count())->toBe(1)
        ->and($project->fresh()->responses()->where('key', 'intro_1')->first()->value)->toBe('New Value');
});

it('throws exception when saving responses without project', function (): void {
    $service = new ProjectService;
    $service->setSteps(ProjectStep::Intro);

    expect(fn () => $service->saveResponses(['intro_1' => 'value']))->toThrow(Exception::class, 'No project set.');
});

it('gets responses as flat array', function (): void {
    $team = Team::factory()->create();
    $project = Project::factory()->create();
    $project->teams()->attach($team);
    $project->responses()->createMany([
        ['step' => 'intro', 'key' => 'intro_1', 'value' => 'Value 1'],
        ['step' => 'intro', 'key' => 'intro_2', 'value' => '123'],
        ['step' => 'intro', 'key' => 'intro_3', 'value' => '45.67'],
    ]);

    $service = new ProjectService;
    $service->setProject($project)->setSteps(ProjectStep::Intro);

    $result = $service->getResponsesArray(false);

    expect($result)->toBeArray()
        ->and($result['intro_1'])->toBe('Value 1')
        ->and($result['intro_2'])->toBe(123) // cast to int
        ->and($result['intro_3'])->toBe(45.67); // cast to float
});

it('gets responses as grouped array', function (): void {
    $team = Team::factory()->create();
    $project = Project::factory()->create();
    $project->teams()->attach($team);
    $project->responses()->createMany([
        ['step' => 'intro', 'key' => 'intro_1', 'value' => 'Value 1'],
        ['step' => 'section-a', 'key' => 'section_a_1', 'value' => 'Value A1'],
    ]);

    $service = new ProjectService;
    $service->setProject($project)->setSteps(ProjectStep::Intro, ProjectStep::SectionA);

    $result = $service->getResponsesArray(true);

    expect($result)->toBeArray()
        ->and($result['intro']['intro_1'])->toBe('Value 1')
        ->and($result['section-a']['section_a_1'])->toBe('Value A1');
});

it('publishes project successfully', function (): void {
    $team = Team::factory()->create();
    $project = Project::factory()->create(['status' => ProjectStatus::Draft]);
    $project->teams()->attach($team);
    $service = new ProjectService;

    $service->setProject($project);
    $result = $service->publishProject();

    expect($result)->toBeTrue()
        ->and($project->fresh()->status)->toBe(ProjectStatus::Published);

    Log::shouldHaveReceived('info')->with('Project published successfully', \Mockery::on(fn ($arg): bool => isset($arg['project_id'])));
});

it('throws exception when publishing without project', function (): void {
    $service = new ProjectService;

    expect(fn (): bool => $service->publishProject())->toThrow(Exception::class, 'No project set.');
});

it('checks if project is complete when published', function (): void {
    $team = Team::factory()->create();
    $project = Project::factory()->create();
    $project->teams()->attach($team);
    $project->status = ProjectStatus::Published;
    $project->save();
    $service = new ProjectService;

    $service->setProject($project->fresh());

    expect($service->isProjectComplete())->toBeTrue();
});

it('checks if project is not complete when not published', function (): void {
    $team = Team::factory()->create();
    $project = Project::factory()->create(['status' => ProjectStatus::Draft]);
    $project->teams()->attach($team);
    $service = new ProjectService;

    $service->setProject($project);

    expect($service->isProjectComplete())->toBeFalse();
});

it('throws exception when checking completion without project', function (): void {
    $service = new ProjectService;

    expect(fn (): bool => $service->isProjectComplete())->toThrow(Exception::class, 'No project set.');
});

it('gets all projects for a team', function (): void {
    $team = Team::factory()->create();
    $projects = Project::factory()->count(3)->create();
    foreach ($projects as $project) {
        $project->teams()->attach($team);
    }

    $service = new ProjectService;
    $teamProjects = $service->getProjectsByTeam($team);

    expect($teamProjects)->toHaveCount(3)
        ->and($teamProjects->first())->toBeInstanceOf(Project::class);
});
