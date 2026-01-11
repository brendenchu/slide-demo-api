<?php

namespace App\Http\Controllers\API\Story;

use App\Enums\Story\ProjectStatus;
use App\Http\Controllers\API\ApiController;
use App\Http\Resources\API\Story\ProjectResource;
use App\Models\Story\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompleteProjectController extends ApiController
{
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $project = Project::where('public_id', $id)->firstOrFail();

        // Authorization check
        if ($project->user_id !== auth()->id()) {
            return $this->forbidden('You do not have access to this project');
        }

        // Update project to completed status
        $project->update([
            'status' => ProjectStatus::Completed->value,
            'current_step' => 'complete',
        ]);

        return $this->success(new ProjectResource($project->fresh(['user.profile', 'teams'])), 'Project completed successfully');
    }
}
