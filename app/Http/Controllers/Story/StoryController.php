<?php

namespace App\Http\Controllers\Story;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Services\TokenService;
use Exception;

class StoryController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(TokenService $tokenService)
    {
        // if published token exists, redirect to complete story page
        if ($token = $tokenService
            ->bypassExpiration()
            ->bypassRevocation()
            ->getTokenByProjectStatus(ProjectStatus::PUBLISHED)) {
            return to_route('story.complete', [
                'project' => $token->project,
                'token' => $token,
            ]);
        }

        // if no draft token exists, redirect to create story page
        if (! ($token = $tokenService
            ->bypassExpiration()
            ->bypassRevocation()
            ->getTokenByProjectStatus(ProjectStatus::DRAFT))) {
            return to_route('story.create');
        }

        $tokenService->setToken($token);

        // if token is expired or revoked, refresh token
        if ($tokenService->isExpired() || $tokenService->isRevoked()) {
            $token = $tokenService->refreshToken();
        }

        // redirect to continue story page
        return to_route('story.continue', [
            'project' => $token->project,
        ]);
    }
}
