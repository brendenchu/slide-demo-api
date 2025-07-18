<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Enums\ProjectStep;
use App\Models\Account\Team;
use App\Models\Story\Project;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ProjectService
{
    /**
     * The project.
     */
    protected Project $project;

    /**
     * The steps.
     *
     * @var array<ProjectStep>
     */
    protected array $steps = [];

    /**
     * Set the project.
     */
    public function setProject(Project|string $project): ProjectService
    {

        $this->project = $project instanceof Project
            ? $project
            : Project::where('public_id', $project)->first();

        return $this;
    }

    /**
     * Get the project.
     *
     * @throws Exception
     */
    public function getProject(): Project
    {
        if (empty($this->project)) {
            throw new Exception('No project set.');
        }

        return $this->project;
    }

    /**
     * Set the step.
     */
    public function setSteps(ProjectStep|string ...$steps): ProjectService
    {
        foreach ($steps as $step) {
            if ($step instanceof ProjectStep) {
                $this->steps[] = $step;
            } else {
                $this->steps[] = ProjectStep::from($step);
            }
        }

        return $this;
    }

    /**
     * Create a new story.
     */
    public function createProject(Team $team, array $validated = []): Project
    {
        if (empty($validated)) {
            $validated = [
                'name' => 'My Project - ' . Carbon::now()->format('Y-m-d H:i:s'),
                'description' => 'This is a form submission for ' . $team->label . '.',
            ];
        }

        // Create new project
        return $team->projects()->create([
            'key' => Str::slug($validated['name'] . '-' . Str::random(6)),
            'label' => $validated['name'],
            'description' => $validated['description'],
        ]);

    }

    /**
     * Get the responses grouped by step.
     *
     * @throws Exception
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

        // cast each response value  to the correct type
        $responses->transform(function ($response) {
            if (is_numeric($response->value)) {
                // has decimal, cast as float, otherwise cast as int
                $response->value = str_contains($response->value, '.') ? (float) $response->value : (int) $response->value;
            } elseif (is_bool($response->value)) {
                $response->value = true;
            }

            return $response;
        });

        // return responses
        return $responses;
    }

    /**
     * Get the responses grouped by step.
     *
     * @throws Exception
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
     * Save the responses for a step.
     *
     * @throws Exception
     */
    public function saveResponses(array $responses): void
    {
        if (empty($this->project)) {
            throw new Exception('No project set.');
        }

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
    }

    /**
     * Publish the project.
     *
     * @throws Exception
     */
    public function publishProject(): bool
    {
        if (empty($this->project)) {
            throw new Exception('No project set.');
        }

        $this->project->status = ProjectStatus::PUBLISHED;

        return $this->project->save();

    }

    /**
     * Check if the project is complete.
     *
     * @throws Exception
     */
    public function isProjectComplete(): bool
    {
        if (empty($this->project)) {
            throw new Exception('No project set.');
        }

        return $this->project->status === ProjectStatus::PUBLISHED;
    }

    public function getProjectsByTeam(Team $team): Collection
    {
        return $team->projects()->get();
    }
}
