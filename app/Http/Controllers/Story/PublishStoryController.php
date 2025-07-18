<?php

namespace App\Http\Controllers\Story;

use App\Http\Controllers\Controller;
use App\Mail\StoryPublished;
use App\Services\ProjectService;
use App\Services\TokenService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PublishStoryController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(
        Request $request,
        ProjectService $projectService,
        TokenService $tokenService,
    ): RedirectResponse {

        // set the project, and get the project
        $project = $projectService->setProject($request->project['id'])->getProject();

        // if any of the required fields are missing, flash an error message
        if (! $tokenService->verifyToken($project)) {
            // flash error message
            session()->flash('error', 'User token is invalid.');

            // redirect to create story page
            return to_route('story.create');
        }

        if (! $projectService->isProjectComplete()) {
            // set the project to complete
            if ($projectService->publishProject() === false) {
                // flash error message
                session()->flash('error', 'Unable to complete project.');

                // redirect to create story page
                return to_route('story.create');
            }

            // save the last position of complete
            $tokenService
                ->setToken($request->token)
                ->saveLastPosition('complete');

            // send email notification to admin
            //            Mail::to(config('mail.from.address'))
            //                ->send(new StoryPublished($tokenService->getToken($project)->user));

            // flash success message
            session()->flash('success', 'Your form has been submitted.');
        }

        // redirect to create story page
        return to_route('story.complete', [
            'project' => $project->public_id,
            'token' => $request->token,
        ]);
    }
}
