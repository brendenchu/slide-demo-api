<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountService;
use App\Services\ProjectService;
use App\Services\TokenService;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class BaseUserController extends Controller
{
    /**
     * @throws Exception
     */
    public function __construct(
        protected readonly AccountService $accountService,
        protected readonly ProjectService $projectService,
        protected readonly TokenService $tokenService,
        protected User $user,
        protected Collection $projects,
        protected Collection $tokens,

    ) {
        //
    }

    /**
     * Set up the user.
     *
     * @throws Exception
     */
    protected function setupUser(string $identifier): void
    {
        // get the user by slug or email
        $this->user = $this->accountService->setUser($identifier)->getUser();

        // get the projects that belong to the user's team
        $this->projects = $this->projectService->getProjectsByTeam($this->user->currentTeam());

        // get the tokens that belong to the user
        $this->projects->each(function ($project): void {
            // push existing tokens to the tokens collection
            if ($existingTokens = $project->tokens()->where('user_id', auth()->id())->first()) {
                $this->tokens->push($existingTokens);
            }

            // push new tokens to the tokens collection for user that is not the current user
            if ($this->user !== auth()->user() && (! $this->tokenService->hasToken($project) && $this->tokenService->hasToken($project, $this->user))) {
                $this->tokens->push($this->tokenService->createToken($project));
            }

        });

    }
}
