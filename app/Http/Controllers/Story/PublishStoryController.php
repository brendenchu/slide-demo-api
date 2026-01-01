<?php

namespace App\Http\Controllers\Story;

use App\Http\Controllers\Controller;
use App\Services\ProjectService;
use App\Services\TokenService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublishStoryController extends Controller
{
    /**
     * Publish a story project.
     *
     * This action is idempotent - if the project is already published,
     * it will skip publishing and redirect to the completion page with
     * an informational message.
     *
     * @param  Request  $request  The HTTP request
     * @param  ProjectService  $projectService  Service for project operations
     * @param  TokenService  $tokenService  Service for token verification
     * @return RedirectResponse Redirect to completion page or create page on error
     *
     * @throws Exception If project operations fail
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

        // Idempotency check: only publish if not already complete
        if ($projectService->isProjectComplete()) {
            // Project already published - inform user and redirect to completion page
            session()->flash('info', 'This project has already been submitted.');

            return to_route('story.complete', [
                'project' => $project->public_id,
                'token' => $request->token,
            ]);
        }

        // Publish the project
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

        // TODO: Enable email notifications when mail configuration is ready
        // This will send an email to the admin when a story is published
        // Uncomment the following lines and ensure MAIL_* env vars are configured:
        // Mail::to(config('mail.from.address'))
        //     ->send(new StoryPublished($tokenService->getToken($project)->user));

        // flash success message
        session()->flash('success', 'Your form has been submitted.');

        // redirect to completion page
        return to_route('story.complete', [
            'project' => $project->public_id,
            'token' => $request->token,
        ]);
    }
}
