<?php

namespace App\Http\Controllers\Story;

use App\Enums\ProjectStatus;
use App\Enums\ProjectStep;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
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
     * @throws Exception
     */
    public function __invoke(
        Request $request,
        TokenService $tokenService,
        Project $project
    ): Response|RedirectResponse {

        // if published token exists, redirect to complete story page
        if ($token = $tokenService
            ->bypassExpiration()
            ->bypassRevocation()
            ->getTokenByProjectStatus(ProjectStatus::PUBLISHED, $project)) {

            return to_route('story.complete', [
                'project' => $token->project,
                'token' => $token,
            ]);
        }

        // if no valid token exists, flash an error message and redirect to dashboard
        if (! $tokenService->verifyToken($project)) {
            // flash error message
            session()->flash('error', 'Sorry, you do not have access to this form.');

            // redirect to create story page
            return to_route('story.create');
        }

        // get last position from token
        $lastPosition = $tokenService->getToken($project)->setting('last_position') ?? [
            'step' => 'intro',
            'page' => 1,
        ];

        // get step from last position
        $step = ProjectStep::from($lastPosition['step']);

        // render story page
        return Inertia::render('Story/ContinueStory', [
            'project' => ProjectResource::make($project),
            'step' => [
                'id' => $step->value,
                'slug' => $step->slug(),
                'name' => $step->label(),
            ],
            'position' => $lastPosition,
        ]);
    }
}
