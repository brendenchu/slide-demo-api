<?php

namespace App\Console\Commands;

use App\Models\Account\Team;
use App\Models\Notification;
use App\Models\Story\Project;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Laravel\Sanctum\PersonalAccessToken;

class DemoResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the demo user data and re-seed demo content';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! config('demo.enabled')) {
            $this->components->error('Demo mode is not enabled.');

            return self::FAILURE;
        }

        $demoUser = User::where('email', config('demo.demo_user_email'))->first();

        if (! $demoUser) {
            $this->components->error('Demo user not found.');

            return self::FAILURE;
        }

        $this->components->info('Resetting demo data...');

        $visitorIds = User::where('id', '!=', $demoUser->id)
            ->where('email', 'not like', '%@example.com')
            ->pluck('id');

        $this->deleteVisitorUsers($visitorIds);
        $this->cleanSeededContent($demoUser);
        $this->cleanOrphanedTeams();
        $this->resetDemoUser($demoUser);

        $this->components->info('Re-seeding demo data...');
        $this->call('db:seed', ['--class' => DatabaseSeeder::class]);

        $this->components->info('Demo reset complete.');

        return self::SUCCESS;
    }

    /**
     * Delete visitor-created users and their non-cascading related data.
     *
     * DB cascades handle: profiles, users_teams, projects, teams_projects,
     * team_invitations (invited_by). These tables lack FK cascades and need
     * manual cleanup: personal_access_tokens, account_terms_agreements,
     * model_has_roles.
     *
     * @param  Collection<int, int>  $userIds
     */
    private function deleteVisitorUsers(Collection $userIds): void
    {
        if ($userIds->isEmpty()) {
            return;
        }

        $this->components->task('Cleaning visitor Sanctum tokens', function () use ($userIds): void {
            PersonalAccessToken::where('tokenable_type', User::class)
                ->whereIn('tokenable_id', $userIds)
                ->delete();
        });

        $this->components->task('Cleaning visitor terms agreements', function () use ($userIds): void {
            \DB::table('account_terms_agreements')
                ->where('accountable_type', User::class)
                ->whereIn('accountable_id', $userIds)
                ->delete();
        });

        $this->components->task('Cleaning visitor role assignments', function () use ($userIds): void {
            \DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->whereIn('model_id', $userIds)
                ->delete();
        });

        $this->components->task('Deleting visitor users', function () use ($userIds): void {
            User::whereIn('id', $userIds)->each(fn (User $user) => $user->delete());
        });
    }

    /**
     * Remove all seeded and transient content before re-seeding.
     *
     * Deletes non-personal teams (cascades: users_teams pivot, teams_projects,
     * team_invitations), demo projects, all notifications, and demo user tokens
     * and terms agreements.
     */
    private function cleanSeededContent(User $demoUser): void
    {
        $this->components->task('Deleting non-personal teams', function (): void {
            Team::where('is_personal', false)->delete();
        });

        $this->components->task('Deleting demo projects', function () use ($demoUser): void {
            Project::where('user_id', $demoUser->id)->each(function (Project $project): void {
                $project->teams()->detach();
                $project->delete();
            });
        });

        $this->components->task('Deleting all notifications', function (): void {
            Notification::query()->delete();
        });

        $this->components->task('Removing demo terms acceptance', function () use ($demoUser): void {
            $demoUser->terms_agreements()->delete();
        });

        $this->components->task('Revoking demo Sanctum tokens', function () use ($demoUser): void {
            $demoUser->tokens()->delete();
        });
    }

    /**
     * Delete teams with no remaining users (safety net for edge cases).
     */
    private function cleanOrphanedTeams(): void
    {
        $this->components->task('Cleaning orphaned teams', function (): void {
            Team::whereDoesntHave('users')->delete();
        });
    }

    /**
     * Reset the demo user's credentials and profile to config defaults.
     */
    private function resetDemoUser(User $demoUser): void
    {
        $this->components->task('Resetting demo user credentials', function () use ($demoUser): void {
            $demoUser->update([
                'name' => config('demo.demo_user_name'),
                'password' => config('demo.demo_user_password'),
            ]);

            [$first, $last] = explode(' ', $demoUser->name, 2);

            $demoUser->profile->update([
                'first_name' => $first,
                'last_name' => $last ?? '',
            ]);
        });
    }
}
