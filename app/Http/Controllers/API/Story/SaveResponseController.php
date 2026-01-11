<?php

namespace App\Http\Controllers\API\Story;

use App\Enums\Story\ProjectStatus;
use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Story\SaveResponseRequest;
use App\Http\Resources\API\Story\ProjectResource;
use App\Models\Story\Project;
use Illuminate\Http\JsonResponse;

class SaveResponseController extends ApiController
{
    public function __invoke(SaveResponseRequest $request, string $id): JsonResponse
    {
        $project = Project::where('public_id', $id)->firstOrFail();

        // Authorization check
        if ($project->user_id !== auth()->id()) {
            return $this->forbidden('You do not have access to this project');
        }

        // Get existing responses
        $responses = $project->responses ?? [];

        // Merge new responses for the given step
        $responses[$request->input('step')] = $request->input('responses');

        // Update project
        $project->update([
            'responses' => $responses,
            'current_step' => $request->input('step'),
            'status' => ProjectStatus::InProgress->value,
        ]);

        return $this->success(new ProjectResource($project->fresh(['user.profile', 'teams'])), 'Responses saved successfully');
    }
}
