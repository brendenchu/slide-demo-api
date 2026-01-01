<?php

namespace App\Http\Controllers\Story;

use App\Enums\Story\ProjectStatus;
use App\Enums\Story\ProjectStep;
use App\Http\Controllers\Controller;
use App\Http\Resources\Story\ProjectResource;
use App\Models\Story\Project;
use App\Services\TokenService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContinueStoryController extends Controller
{
    /**
     * Continue working on an existing story project.
     *
     * Retrieves the user's last position and renders the continue page.
     * Redirects to completion page if project is already published.
     *
     * @param  Request  $request  The HTTP request
     * @param  TokenService  $tokenService  Service for token operations
     * @param  Project  $project  The project to continue (route model binding)
     * @return Response|RedirectResponse Inertia response or redirect
     *
     * @throws Exception If token or step operations fail
     */
    public function __invoke(
        Request $request,
        TokenService $tokenService,
        Project $project
    ): Response|RedirectResponse {

        // if published token exists, redirect to complete story page
        if ($publishedToken = $tokenService
            ->bypassExpiration()
            ->bypassRevocation()
            ->getTokenByProjectStatus(ProjectStatus::PUBLISHED, $project)) {

            return to_route('story.complete', [
                'project' => $publishedToken->project,
                'token' => $publishedToken,
            ]);
        }

        // if no valid token exists, flash an error message and redirect to dashboard
        if (! $tokenService->verifyToken($project)) {
            session()->flash('error', 'Sorry, you do not have access to this form.');

            return to_route('story.create');
        }

        // Get token once and reuse (avoid duplicate queries)
        $token = $tokenService->getToken($project);

        // get last position from token with default fallback
        $lastPosition = $token->setting('last_position') ?? [
            'step' => 'intro',
            'page' => 1,
        ];

        // Validate and get step from last position
        try {
            $step = ProjectStep::from($lastPosition['step']);
        } catch (\ValueError) {
            // Invalid step in last position, default to intro
            $step = ProjectStep::Intro;
            $lastPosition = ['step' => 'intro', 'page' => 1];
        }

        // render story page
        return Inertia::render('Story/ContinueStory', [
            'project' => ProjectResource::make($project),
            'step' => [
                'id' => $step->value,
                'slug' => $step->slug(),
                'name' => $step->label(),
            ],
            'token' => $token->public_id,
            'position' => $lastPosition,
        ]);
    }
}
