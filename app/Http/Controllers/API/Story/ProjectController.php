<?php

namespace App\Http\Controllers\API\Story;

use App\Enums\Story\ProjectStatus;
use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Story\CreateProjectRequest;
use App\Http\Requests\API\Story\UpdateProjectRequest;
use App\Http\Resources\API\Story\ProjectResource;
use App\Models\Story\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::query()
            ->with('user.profile', 'teams')
            ->where('user_id', $request->user()->id);

        // Scope to user's current team
        $currentTeam = $request->user()->currentTeam();
        if ($currentTeam) {
            $query->whereHas('teams', fn ($q) => $q->where('teams.id', $currentTeam->id));
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $statusValue = match ($request->input('status')) {
                'draft' => ProjectStatus::Draft->value,
                'in_progress' => ProjectStatus::InProgress->value,
                'completed' => ProjectStatus::Completed->value,
                default => null,
            };

            if ($statusValue) {
                $query->where('status', $statusValue);
            }
        }

        // Search in title/label
        if ($request->filled('search')) {
            $query->where('label', 'like', '%' . $request->input('search') . '%');
        }

        $projects = $query->latest('updated_at')->get();

        return $this->success(ProjectResource::collection($projects));
    }

    public function show(string $id): JsonResponse
    {
        $project = Project::with('user.profile', 'teams')
            ->where('public_id', $id)
            ->firstOrFail();

        // Authorization check
        if ($project->user_id !== auth()->id() && ! auth()->user()->hasRole(['admin', 'super-admin'])) {
            return $this->forbidden('You do not have access to this project');
        }

        return $this->success(new ProjectResource($project));
    }

    public function store(CreateProjectRequest $request): JsonResponse
    {
        $project = Project::create([
            'user_id' => $request->user()->id,
            'key' => Str::slug($request->input('title')) . '-' . Str::random(6),
            'label' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => ProjectStatus::Draft->value,
            'current_step' => 'intro',
            'responses' => [],
        ]);

        $currentTeam = $request->user()->currentTeam();
        if ($currentTeam) {
            $project->teams()->attach($currentTeam);
        }

        return $this->created(new ProjectResource($project->load('teams')), 'Project created successfully');
    }

    public function update(UpdateProjectRequest $request, string $id): JsonResponse
    {
        $project = Project::where('public_id', $id)->firstOrFail();

        // Authorization check
        if ($project->user_id !== auth()->id() && ! auth()->user()->hasRole(['admin', 'super-admin'])) {
            return $this->forbidden('You do not have access to this project');
        }

        $data = [];

        if ($request->filled('title')) {
            $data['label'] = $request->input('title');
        }

        if ($request->has('description')) {
            $data['description'] = $request->input('description');
        }

        if ($request->filled('status')) {
            $data['status'] = match ($request->input('status')) {
                'draft' => ProjectStatus::Draft->value,
                'in_progress' => ProjectStatus::InProgress->value,
                'completed' => ProjectStatus::Completed->value,
                default => $project->status,
            };
        }

        if ($request->filled('current_step')) {
            $data['current_step'] = $request->input('current_step');
        }

        $project->update($data);

        return $this->success(new ProjectResource($project->fresh(['user.profile', 'teams'])), 'Project updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $project = Project::where('public_id', $id)->firstOrFail();

        // Authorization check
        if ($project->user_id !== auth()->id() && ! auth()->user()->hasRole(['admin', 'super-admin'])) {
            return $this->forbidden('You do not have access to this project');
        }

        $project->delete();

        return $this->noContent();
    }
}
