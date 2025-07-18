<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Story\Project;
use App\Models\Story\Token;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TokenService
{
    /**
     * The token.
     */
    protected Token $token;

    /**
     * Bypass expiration.
     */
    protected bool $bypassExpiration = false;

    /**
     * Bypass revocation.
     */
    protected bool $bypassRevocation = false;

    /**
     * Set the token.
     */
    public function setToken(Token|string $token): TokenService
    {
        $this->token = $token instanceof Token
            ? $token
            : Token::where('public_id', $token)->first();

        return $this;
    }

    /**
     * Get the token by project.
     */
    public function getToken(Project $project, ?User $user = null): Token|Model|null
    {
        $user ??= auth()->user();

        return $this->setupQuery()
            ->where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->first();
    }

    /**
     * Verify that the token is valid.
     */
    public function verifyToken(Project $project, ?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->setupQuery()
            ->where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->exists();
    }

    /**
     * Create a token.
     */
    public function createToken(Project $project, ?User $user = null): Token
    {
        $user ??= auth()->user();

        return Token::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'expires_at' => Carbon::now()->addDays(7),
        ]);
    }

    /**
     * @throws Exception
     */
    public function saveLastPosition(string $step, ?int $page = null): void
    {
        if (empty($this->token)) {
            throw new Exception('No token set.');
        }

        // write last position to session
        session()->put('last_position', [
            'step' => $step,
            'page' => $page,
        ]);

        // write last position to token settings
        $this->token->setSetting('last_position', [
            'step' => $step,
            'page' => $page,
        ]);
    }

    public function bypassExpiration(): TokenService
    {
        $this->bypassExpiration = true;

        return $this;
    }

    public function bypassRevocation(): TokenService
    {
        $this->bypassRevocation = true;

        return $this;
    }

    /**
     * Set up the token builder query.
     */
    private function setupQuery(): Builder
    {
        $query = Token::query();

        if (! empty($this->token)) {
            $query->where('public_id', $this->token->public_id);
        }

        // Let's ignore expiration of tokens for now

        //        if (! $this->bypassExpiration) {
        //            $query->where('expires_at', '>', Carbon::now());
        //        }

        if (! $this->bypassRevocation) {
            $query->whereNull('revoked_at');
        }

        return $query;
    }

    /**
     * Get the latest token by project status
     *
     * @throws Exception
     */
    public function getTokenByProjectStatus(ProjectStatus $status, ?Project $project = null, ?User $user = null): Token|Model|null
    {
        $user ??= auth()->user();
        $project ??= $this->token->project ?? null;

        return $this->setupQuery()
            ->where('user_id', $user->id)
            ->when($project, function (Builder $query) use ($project) {
                $query->where('project_id', $project->id);
            })
            ->whereHas('project', function (Builder $query) use ($status) {
                $query->where('status', $status->value);
            })
            ->latest()
            ->first();
    }

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        return $this->token->expires_at->isPast();
    }

    /**
     * Check if the token is revoked.
     */
    public function isRevoked(): bool
    {
        return ! empty($this->token->revoked_at);
    }

    /**
     * Refresh the token.
     *
     * @throws Exception
     */
    public function refreshToken(): Token
    {
        if (empty($this->token)) {
            throw new Exception('No token set.');
        }

        // revoke current token
        $this->token->update([
            'revoked_at' => Carbon::now(),
        ]);

        // create new token
        $newToken = $this->createToken($this->token->project);

        // copy settings from old token to new token
        $newToken->update([
            'settings' => $this->token->settings,
        ]);

        // set new token
        $this->token = $newToken;

        // return new token
        return $this->token;
    }

    public function hasToken(Project $project, ?User $user = null): Token|Model|null
    {
        $user ??= auth()->user();

        return $this->setupQuery()
            ->where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->first();
    }
}
