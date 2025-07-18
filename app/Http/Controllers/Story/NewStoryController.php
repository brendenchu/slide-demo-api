<?php

namespace App\Http\Controllers\Story;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Services\ProjectService;
use App\Services\TokenService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewStoryController extends Controller
{
    /**
     * Display the story intro page.
     *
     * @throws Exception
     */
    public function create(TokenService $tokenService): Response|RedirectResponse
    {
        // if draft token exists, redirect to continue story page
        if ($token = $tokenService
            ->bypassExpiration()
            ->bypassRevocation()
            ->getTokenByProjectStatus(ProjectStatus::DRAFT)) {
            return to_route('story.continue', [
                'project' => $token->project,
                'token' => $token,
            ]);
        }

        return Inertia::render('Story/NewStory');
    }

    public function store(Request $request, ProjectService $projectService, TokenService $tokenService): RedirectResponse
    {
        // create new project
        $project = $projectService->createProject($request->user()->currentTeam());

        // create story token
        $token = $tokenService->createToken($project);

        // redirect to first section of story form
        return to_route('story.form', [
            'project' => $project,
            'step' => 'intro',
            'token' => $token->public_id,
        ]);
    }
}
