<?php

namespace App\Services;

use App\Enums\Story\ProjectStatus;
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
     * The currently active token for this service instance.
     */
    protected Token $token;

    /**
     * Whether to bypass token revocation checks.
     *
     * When true, revoked tokens will still be considered valid.
     */
    protected bool $bypassRevocation = false;

    /**
     * Set the active token by instance or public ID.
     *
     * @param  Token|string  $token  Token instance or public_id
     * @return self Fluent interface for method chaining
     */
    public function setToken(Token|string $token): self
    {
        $this->token = $token instanceof Token
            ? $token
            : Token::where('public_id', $token)->first();

        return $this;
    }

    /**
     * Get a token for the given project and user.
     *
     * Applies standard filters (revocation, expiration) via setupQuery().
     *
     * @param  Project  $project  The project to find token for
     * @param  User|null  $user  The user (defaults to authenticated user)
     * @return Token|Model|null The token if found, null otherwise
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
     * Verify that a valid token exists for the project and user.
     *
     * More efficient than getToken() when you only need to check existence.
     *
     * @param  Project  $project  The project to verify token for
     * @param  User|null  $user  The user (defaults to authenticated user)
     * @return bool True if valid token exists, false otherwise
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
     * Create a new token for the given project and user.
     *
     * Token expires after 7 days by default.
     *
     * @param  Project  $project  The project to create token for
     * @param  User|null  $user  The user (defaults to authenticated user)
     * @return Token The newly created token
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
     * Save the user's last position in the form flow.
     *
     * Stores position in token settings for persistence.
     * (Session storage removed for API-only mode)
     *
     * @param  string  $step  The step identifier
     * @param  int|null  $page  The page number within the step
     *
     * @throws Exception If no token has been set
     */
    public function saveLastPosition(string $step, ?int $page = null): void
    {
        if (empty($this->token)) {
            throw new Exception('No token set.');
        }

        // write last position to token settings
        $this->token->setSetting('last_position', [
            'step' => $step,
            'page' => $page,
        ]);
    }

    /**
     * Bypass revocation checks for subsequent token queries.
     *
     * Useful for token refresh flows or administrative operations.
     *
     * @return self Fluent interface for method chaining
     */
    public function bypassRevocation(): self
    {
        $this->bypassRevocation = true;

        return $this;
    }

    /**
     * Set up the token builder query with optional filters.
     *
     * Applies standard query constraints based on current service state:
     * - Filters by current token if set
     * - Filters out revoked tokens unless bypass is enabled
     * - Expiration check is currently disabled
     *
     * Changed from private to protected for better testability and extensibility.
     *
     * @return Builder Query builder with applied filters
     */
    protected function setupQuery(): Builder
    {
        $query = Token::query();

        if (! empty($this->token)) {
            $query->where('public_id', $this->token->public_id);
        }

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
            ->when($project, function (Builder $query) use ($project): void {
                $query->where('project_id', $project->id);
            })
            ->whereHas('project', function (Builder $query) use ($status): void {
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

    /**
     * Check if a token exists for the project and user.
     *
     * This is an alias for getToken() for better semantic clarity when
     * checking token existence.
     *
     * @param  Project  $project  The project to check
     * @param  User|null  $user  The user (defaults to authenticated user)
     * @return Token|Model|null The token if found, null otherwise
     */
    public function hasToken(Project $project, ?User $user = null): Token|Model|null
    {
        return $this->getToken($project, $user);
    }
}
