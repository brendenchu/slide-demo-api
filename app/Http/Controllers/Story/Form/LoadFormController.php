<?php

namespace App\Http\Controllers\Story\Form;

use App\Enums\ProjectStep;
use App\Http\Resources\ProjectResource;
use App\Models\Story\Project;
use App\Services\ProjectService;
use App\Services\TokenService;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoadFormController
{
    /**
     * @throws Exception
     */
    public function __invoke(
        Request $request,
        ProjectService $projectService,
        TokenService $tokenService,
        Project $project,
        ProjectStep $step): Response|\Symfony\Component\HttpFoundation\Response
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

        // get story responses
        $responses = $projectService
            ->setProject($project->public_id)
            ->setSteps($step->value)
            ->getResponsesArray(grouped: true);

        // render story page
        return Inertia::render('Story/StoryForm', [
            'project' => ProjectResource::make($project),
            'step' => [
                'id' => $step->value,
                'slug' => $step->slug(),
                'name' => $step->label(),
            ],
            'token' => $request->token,
            'page' => $request->has('page') ? (int) $request->page : 1,
            'direction' => $request->has('direction') ? (string) $request->direction : 'next',
            'story' => $responses[$step->value],
        ]);
    }
}
