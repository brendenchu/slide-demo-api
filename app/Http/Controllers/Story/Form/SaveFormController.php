<?php

namespace App\Http\Controllers\Story\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\Story\StoryFormRequest;
use App\Services\ProjectService;
use App\Services\TokenService;
use Exception;

/**
 * Class SaveResponsesController
 */
class SaveFormController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(
        StoryFormRequest $request,
        ProjectService $projectService,
        TokenService $tokenService,
    ) {

        // if any of the required fields are missing, flash an error message
        if (! ($request->has('project') && $request->has('step') && $request->has('token'))) {
            // flash error message
            session()->flash('error', 'Invalid form ID or token.');
        }

        // if the user is not a guest, save the responses
        if (! auth()->user()->hasRole('guest')) {
            // save the validated responses
            $projectService
                ->setProject($request->project['id'])
                ->setSteps($request->step['id'])
                ->saveResponses($request->validated());

            // save the last position
            $tokenService
                ->setToken($request->token)
                ->saveLastPosition(
                    $request->step['id'],
                    $request->page
                );
        }

            // flash success message
        session()->flash('success', 'Your responses have been saved.');
    }
}
