<?php

use App\Enums\Story\ProjectStatus;
use App\Models\Story\Project;
use App\Models\Story\Token;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('sets token by instance', function (): void {
    $token = Token::factory()->create();
    $service = new TokenService;

    $result = $service->setToken($token);

    expect($result)->toBeInstanceOf(TokenService::class);
});

it('sets token by public_id string', function (): void {
    $token = Token::factory()->create();
    $service = new TokenService;

    $result = $service->setToken($token->public_id);

    expect($result)->toBeInstanceOf(TokenService::class);
});

it('gets token for project and user', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $token = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $service = new TokenService;
    $result = $service->getToken($project, $user);

    expect($result)->toBeInstanceOf(Token::class)
        ->and($result->id)->toBe($token->id);
});

it('gets token using authenticated user when user is null', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $token = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user);
    $service = new TokenService;
    $result = $service->getToken($project);

    expect($result)->toBeInstanceOf(Token::class)
        ->and($result->id)->toBe($token->id);
});

it('does not get revoked tokens by default', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    Token::factory()->revoked()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $service = new TokenService;
    $result = $service->getToken($project, $user);

    expect($result)->toBeNull();
});

it('verifies token exists', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $service = new TokenService;
    $result = $service->verifyToken($project, $user);

    expect($result)->toBeTrue();
});

it('verifies token does not exist', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $service = new TokenService;
    $result = $service->verifyToken($project, $user);

    expect($result)->toBeFalse();
});

it('creates token with correct expiration', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $service = new TokenService;
    $token = $service->createToken($project, $user);

    $daysDiff = now()->diffInDays($token->expires_at, false);

    expect($token)->toBeInstanceOf(Token::class)
        ->and($token->user_id)->toBe($user->id)
        ->and($token->project_id)->toBe($project->id)
        ->and($token->expires_at->isFuture())->toBeTrue()
        ->and($daysDiff)->toBeGreaterThanOrEqual(6)
        ->and($daysDiff)->toBeLessThan(8);
});

it('creates token using authenticated user when user is null', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $this->actingAs($user);
    $service = new TokenService;
    $token = $service->createToken($project);

    expect($token->user_id)->toBe($user->id);
});

it('saves last position to token settings', function (): void {
    $token = Token::factory()->create();
    $service = new TokenService;
    $service->setToken($token);

    $service->saveLastPosition('intro', 2);

    expect($token->fresh()->setting('last_position'))->toBe([
        'step' => 'intro',
        'page' => 2,
    ]);
});

it('throws exception when saving position without token', function (): void {
    $service = new TokenService;

    expect(fn () => $service->saveLastPosition('intro', 1))->toThrow(Exception::class, 'No token set.');
});

it('bypasses revocation and returns self', function (): void {
    $service = new TokenService;

    $result = $service->bypassRevocation();

    expect($result)->toBeInstanceOf(TokenService::class);
});

it('gets revoked tokens when bypass is enabled', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $token = Token::factory()->revoked()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $service = new TokenService;
    $result = $service->bypassRevocation()->getToken($project, $user);

    expect($result)->toBeInstanceOf(Token::class)
        ->and($result->id)->toBe($token->id);
});

it('gets token by project status', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $project->status = ProjectStatus::Published;
    $project->save();

    $token = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $service = new TokenService;
    $result = $service->getTokenByProjectStatus(ProjectStatus::Published, $project, $user);

    expect($result)->toBeInstanceOf(Token::class)
        ->and($result->id)->toBe($token->id);
});

it('gets latest token by project status', function (): void {
    $user = User::factory()->create();
    $project1 = Project::factory()->create();
    $project1->status = ProjectStatus::Published;
    $project1->save();

    $project2 = Project::factory()->create();
    $project2->status = ProjectStatus::Published;
    $project2->save();

    $olderToken = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project1->id,
        'created_at' => now()->subDays(2),
    ]);

    $newerToken = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project2->id,
        'created_at' => now(),
    ]);

    $service = new TokenService;
    $result = $service->getTokenByProjectStatus(ProjectStatus::Published, null, $user);

    expect($result)->toBeInstanceOf(Token::class)
        ->and($result->id)->toBe($newerToken->id)
        ->and($result->project->status)->toBe(ProjectStatus::Published);
});

it('checks if token is expired', function (): void {
    $token = Token::factory()->expired()->create();
    $service = new TokenService;
    $service->setToken($token);

    expect($service->isExpired())->toBeTrue();
});

it('checks if token is not expired', function (): void {
    $token = Token::factory()->create();
    $service = new TokenService;
    $service->setToken($token);

    expect($service->isExpired())->toBeFalse();
});

it('checks if token is revoked', function (): void {
    $token = Token::factory()->revoked()->create();
    $service = new TokenService;
    $service->setToken($token);

    expect($service->isRevoked())->toBeTrue();
});

it('checks if token is not revoked', function (): void {
    $token = Token::factory()->create();
    $service = new TokenService;
    $service->setToken($token);

    expect($service->isRevoked())->toBeFalse();
});

it('refreshes token by revoking old and creating new', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $oldToken = Token::factory()->create([
        'user_id' => $user->id,
        'settings' => ['last_position' => ['step' => 'intro', 'page' => 1]],
    ]);

    $service = new TokenService;
    $service->setToken($oldToken);
    $newToken = $service->refreshToken();

    expect($newToken)->toBeInstanceOf(Token::class)
        ->and($newToken->id)->not->toBe($oldToken->id)
        ->and($oldToken->fresh()->revoked_at)->not->toBeNull()
        ->and($newToken->settings)->toBe($oldToken->settings)
        ->and($newToken->project_id)->toBe($oldToken->project_id)
        ->and($newToken->user_id)->toBe($oldToken->user_id);
});

it('throws exception when refreshing without token', function (): void {
    $service = new TokenService;

    expect(fn (): Token => $service->refreshToken())->toThrow(Exception::class, 'No token set.');
});

it('has token returns token as alias', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $token = Token::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $service = new TokenService;
    $result = $service->hasToken($project, $user);

    expect($result)->toBeInstanceOf(Token::class)
        ->and($result->id)->toBe($token->id);
});
