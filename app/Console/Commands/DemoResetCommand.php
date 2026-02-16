<?php

namespace App\Console\Commands;

use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Models\Notification;
use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Console\Command;

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
        $this->cleanDemoData($demoUser);

        $this->components->info('Re-seeding demo data...');
        $this->call('db:seed', ['--class' => 'Database\\Seeders\\DemoSeeder']);

        $this->components->info('Demo reset complete.');

        return self::SUCCESS;
    }

    /**
     * Remove all demo-specific data for the given user.
     */
    private function cleanDemoData(User $demoUser): void
    {
        $this->components->task('Deleting demo projects', function () use ($demoUser): void {
            Project::where('user_id', $demoUser->id)->each(function (Project $project): void {
                $project->teams()->detach();
                $project->delete();
            });
        });

        $this->components->task('Deleting demo notifications', function () use ($demoUser): void {
            Notification::where('recipient_id', $demoUser->id)->delete();
        });

        $this->components->task('Deleting demo invitations', function () use ($demoUser): void {
            TeamInvitation::where('user_id', $demoUser->id)
                ->orWhere('email', $demoUser->email)
                ->delete();
        });

        $this->components->task('Cleaning up demo teams', function () use ($demoUser): void {
            $nonPersonalTeams = $demoUser->teams()->where('is_personal', false)->get();

            foreach ($nonPersonalTeams as $team) {
                $demoUser->teams()->detach($team->id);

                // Delete orphaned teams with no remaining users
                if ($team->users()->count() === 0) {
                    $team->delete();
                }
            }

            // Also delete non-personal teams that were created for invitations
            // (teams with invitation team_id referencing deleted invitations may be orphaned)
            Team::where('is_personal', false)
                ->whereDoesntHave('users')
                ->delete();
        });

        $this->components->task('Removing terms acceptance', function () use ($demoUser): void {
            $demoUser->terms_agreements()->delete();
        });
    }
}
