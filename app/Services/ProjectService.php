<?php

namespace App\Services;

use App\Enums\Story\ProjectStatus;
use App\Enums\Story\ProjectStep;
use App\Models\Account\Team;
use App\Models\Story\Project;
use App\Models\Story\Response;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProjectService
{
    /**
     * The currently active project for this service instance.
     */
    protected Project $project;

    /**
     * The project steps to operate on.
     *
     * @var array<ProjectStep>
     */
    protected array $steps = [];

    /**
     * Set the active project by instance or public ID.
     *
     * @param  Project|string  $project  Project instance or public_id
     * @return self Fluent interface for method chaining
     */
    public function setProject(Project|string $project): self
    {
        if ($project instanceof Project) {
            $this->project = $project;
        } else {
            $this->project = Project::where('public_id', $project)->first();

            if (! $this->project) {
                Log::warning('Project not found', ['public_id' => $project]);
            }
        }

        return $this;
    }

    /**
     * Get the currently set project.
     *
     * @return Project The active project instance
     *
     * @throws Exception If no project has been set
     */
    public function getProject(): Project
    {
        if (empty($this->project)) {
            throw new Exception('No project set.');
        }

        return $this->project;
    }

    /**
     * Set one or more project steps to operate on.
     *
     * Accepts variadic arguments of ProjectStep enums or string values.
     *
     * @param  ProjectStep|string  ...$steps  One or more steps to set
     * @return self Fluent interface for method chaining
     */
    public function setSteps(ProjectStep|string ...$steps): self
    {
        foreach ($steps as $step) {
            $this->steps[] = $step instanceof ProjectStep ? $step : ProjectStep::from($step);
        }

        return $this;
    }

    /**
     * Create a new project for the given team.
     *
     * If no validated data is provided, creates project with auto-generated name and description.
     *
     * @param  Team  $team  The team to create the project for
     * @param  array  $validated  Optional validated data containing 'name' and 'description'
     * @return Project The newly created project
     *
     * @throws Exception If project creation fails
     */
    public function createProject(Team $team, array $validated = []): Project
    {
        if ($validated === []) {
            $validated = [
                'name' => 'My Project - ' . Carbon::now()->format('Y-m-d H:i:s'),
                'description' => 'This is a form submission for ' . $team->label . '.',
            ];
        }

        try {
            // Create new project
            $project = $team->projects()->create([
                'key' => Str::slug($validated['name'] . '-' . Str::random(6)),
                'label' => $validated['name'],
                'description' => $validated['description'],
            ]);

            Log::info('Project created successfully', [
                'project_id' => $project->public_id,
                'team_id' => $team->public_id,
            ]);

            return $project;
        } catch (Exception $e) {
            Log::error('Failed to create project', [
                'team_id' => $team->public_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get responses for the current steps with type casting applied.
     *
     * Private helper method that fetches responses and casts values to appropriate types:
     * - Numeric strings → int or float
     * - Boolean-like values → true/false
     *
     * @return Collection Collection of Response models with cast values
     *
     * @throws Exception If no project has been set
     */
    private function getResponsesCollection(): Collection
    {
        if (empty($this->project)) {
            throw new Exception('No project set.');
        }

        // get responses for this step
        $responses = $this->project->responses()->whereIn('step',
            collect($this->steps)->map(fn ($step) => $step->value)->toArray()
        )->get();

        // cast each response value to the correct type
        $responses->transform(fn (Response $response): Response => $this->castResponseValue($response));

        // return responses
        return $responses;
    }

    /**
     * Cast a response value to its appropriate PHP type.
     *
     * Handles automatic type conversion:
     * - Numeric strings with decimals → float
     * - Numeric strings without decimals → int
     * - Boolean values → true
     */
    private function castResponseValue(Response $response): Response
    {
        if (is_numeric($response->value)) {
            // has decimal, cast as float, otherwise cast as int
            $response->value = str_contains($response->value, '.') ? (float) $response->value : (int) $response->value;
        } elseif (is_bool($response->value)) {
            $response->value = true;
        }

        return $response;
    }

    /**
     * Get responses as an associative array.
     *
     * @param  bool  $grouped  If true, groups by step: ['step1' => ['field' => 'value']]
     *                         If false, flat array: ['field' => 'value']
     * @return array Array of responses with type-cast values
     *
     * @throws Exception If no project or steps have been set
     */
    public function getResponsesArray(bool $grouped = false): array
    {
        // initialize responses array
        $array = [];

        // get responses for this step
        $responses = $this->getResponsesCollection();

        // loop through fields for this step, and add to responses array
        foreach ($this->steps as $step) {
            foreach ($step->fields() as $field) {
                $value = $responses->where('key', $field)->first()?->value;
                if ($grouped) {
                    $array[$step->value][$field] = $value;
                } else {
                    $array[$field] = $value;
                }
            }
        }

        // return responses
        return $array;

    }

    /**
     * Save or update responses for the current project.
     *
     * Automatically matches response keys to configured step fields
     * and creates/updates response records accordingly.
     *
     * @param  array  $responses  Associative array of field keys and values
     *
     * @throws Exception If no project has been set
     */
    public function saveResponses(array $responses): void
    {
        if (empty($this->project)) {
            throw new Exception('No project set.');
        }

        try {
            foreach ($responses as $key => $value) {
                foreach ($this->steps as $step) {
                    if (in_array($key, $step->fields())) {
                        $this->project->responses()->updateOrCreate([
                            'step' => $step->value,
                            'key' => $key,
                        ], [
                            'value' => $value,
                        ]);
                        break;
                    }
                }
            }

            Log::info('Project responses saved', [
                'project_id' => $this->project->public_id,
                'response_count' => count($responses),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to save project responses', [
                'project_id' => $this->project->public_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Publish the current project.
     *
     * Changes project status to PUBLISHED and persists to database.
     *
     * @return bool True if successfully saved
     *
     * @throws Exception If no project has been set
     */
    public function publishProject(): bool
    {
        if (empty($this->project)) {
            throw new Exception('No project set.');
        }

        $this->project->status = ProjectStatus::PUBLISHED;

        $saved = $this->project->save();

        if ($saved) {
            Log::info('Project published successfully', [
                'project_id' => $this->project->public_id,
            ]);
        } else {
            Log::error('Failed to publish project', [
                'project_id' => $this->project->public_id,
            ]);
        }

        return $saved;
    }

    /**
     * Check if the current project is complete (published).
     *
     * @return bool True if project status is PUBLISHED
     *
     * @throws Exception If no project has been set
     */
    public function isProjectComplete(): bool
    {
        if (empty($this->project)) {
            throw new Exception('No project set.');
        }

        return $this->project->status === ProjectStatus::PUBLISHED;
    }

    /**
     * Get all projects for the given team.
     *
     * @param  Team  $team  The team to fetch projects for
     * @return Collection Collection of Project models
     */
    public function getProjectsByTeam(Team $team): Collection
    {
        return $team->projects()->get();
    }
}
