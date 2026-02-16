<?php

use App\Enums\Story\ProjectStatus;
use App\Models\Account\TeamInvitation;
use App\Models\Notification;
use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('demo.enabled', true);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    $this->demoUser = User::where('email', config('demo.demo_user_email'))->first();
});

it('fails when demo mode is disabled', function (): void {
    config()->set('demo.enabled', false);

    $this->artisan('demo:reset')
        ->expectsOutputToContain('Demo mode is not enabled')
        ->assertFailed();
});

it('fails when demo user is not found', function (): void {
    $this->demoUser->delete();

    $this->artisan('demo:reset')
        ->expectsOutputToContain('Demo user not found')
        ->assertFailed();
});

it('resets and re-seeds successfully', function (): void {
    $this->artisan('demo:reset')->assertSuccessful();

    expect(Project::where('user_id', $this->demoUser->id)->count())->toBe(3)
        ->and(Notification::where('recipient_id', $this->demoUser->id)->count())->toBe(3)
        ->and(TeamInvitation::where('user_id', $this->demoUser->id)->pending()->count())->toBe(1);
});

it('removes terms acceptance', function (): void {
    // Manually add a terms agreement
    $this->demoUser->terms_agreements()->create([
        'terms_version_id' => config('terms.current_version'),
        'accepted_at' => now(),
    ]);

    expect($this->demoUser->hasAcceptedCurrentTerms())->toBeTrue();

    $this->artisan('demo:reset')->assertSuccessful();

    $this->demoUser->unsetRelation('terms_agreements');
    expect($this->demoUser->hasAcceptedCurrentTerms())->toBeFalse();
});

it('is idempotent — running twice yields exactly 3 projects', function (): void {
    $this->artisan('demo:reset')->assertSuccessful();
    $this->artisan('demo:reset')->assertSuccessful();

    expect(Project::where('user_id', $this->demoUser->id)->count())->toBe(3);
});

it('preserves all users', function (): void {
    $userCountBefore = User::count();

    $this->artisan('demo:reset')->assertSuccessful();

    expect(User::count())->toBe($userCountBefore);
});

it('seeds projects at draft, in-progress, and completed statuses', function (): void {
    $this->artisan('demo:reset')->assertSuccessful();

    $projects = Project::where('user_id', $this->demoUser->id)->get();

    expect($projects->where('status', ProjectStatus::Draft)->count())->toBe(1)
        ->and($projects->where('status', ProjectStatus::InProgress)->count())->toBe(1)
        ->and($projects->where('status', ProjectStatus::Completed)->count())->toBe(1);
});
