<?php

namespace App\Http\Controllers\Story;

use App\Http\Controllers\Controller;
use App\Models\Story\Project;
use App\Services\ProjectService;
use App\Services\TokenService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class StoryDashboardController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(
        Request $request,
        ProjectService $projectService,
        TokenService $tokenService,
        Project $project
    ): RedirectResponse|Response {

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
            ->setSteps(
                'intro',
                'section-a',
                'section-b',
                'section-c',
            )
            ->getResponsesArray();

        // convert null values to zero
        foreach ($responses as $key => $value) {

            if (str_contains((string) $key, '_has_')) {
                unset($responses[$key]);

                continue;
            }

            if (is_null($value)) {
                $responses[$key] = 0;
            }
        }

        // render story page
        return to_route('story.complete', [
            'project' => $project->public_id,
            'token' => $request->token,
        ]);

    }
}
