<?php

namespace App\Http\Controllers\Story;

use App\Enums\ProjectStep;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Story\Project;
use App\Services\ProjectService;
use App\Services\TokenService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompleteStoryController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(Request $request, ProjectService $projectService, TokenService $tokenService, Project $project): Response|RedirectResponse
    {
        // if no token in query string
        if (! $request->has('token')) {
            // flash error message
            session()->flash('error', 'Token is required.');

            // redirect to create story page
            return to_route('story.create');
        }

        // if any of the required fields are missing, flash an error message
        if (! $tokenService
            ->setToken($request->token)
            ->verifyToken($project)) {
            // flash error message
            session()->flash('error', 'User token is invalid.');

            // redirect to create story page
            return to_route('story.create');
        }

        // if project is not complete, flash an error message
        if (! $projectService->setProject($project)->isProjectComplete()) {
            // flash error message
            session()->flash('error', 'Project is not complete.');

            return to_route('story');
        }

        $step = ProjectStep::COMPLETE;

        return Inertia::render('Story/CompleteStory', [
            'project' => ProjectResource::make($project),
            'step' => [
                'id' => $step->value,
                'slug' => $step->slug(),
                'name' => $step->label(),
            ],
            'token' => $tokenService->getToken($project)->public_id,
            'allSteps' => ProjectStep::allSteps(),
        ]);
    }
}
