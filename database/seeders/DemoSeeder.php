<?php

namespace Database\Seeders;

use App\Enums\Account\InvitationStatus;
use App\Enums\Account\TeamRole;
use App\Enums\Account\TeamStatus;
use App\Enums\Story\ProjectStatus;
use App\Enums\Story\ProjectStep;
use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Models\Notification;
use App\Models\Story\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Seed demo data for the demo user.
     */
    public function run(): void
    {
        $demoUser = User::where('email', config('demo.demo_user_email'))->first();

        if (! $demoUser) {
            $this->command->warn('Demo user not found — skipping DemoSeeder.');

            return;
        }

        $dummyUsers = User::where('email', '!=', config('demo.demo_user_email'))
            ->where('email', 'like', '%@example.com')
            ->orderBy('id')
            ->get();

        $this->removeTermsAcceptance($demoUser);
        $this->seedProjects($demoUser);
        $this->seedSharedTeam($demoUser, $dummyUsers);
        $this->seedPendingInvitation($demoUser, $dummyUsers);
        $this->seedNotifications($demoUser, $dummyUsers);
    }

    /**
     * Delete any terms agreements so the terms gate is visible on first login.
     */
    private function removeTermsAcceptance(User $demoUser): void
    {
        $demoUser->terms_agreements()->delete();
    }

    /**
     * Create 3 projects on the demo user's personal team at varying statuses.
     */
    private function seedProjects(User $demoUser): void
    {
        $personalTeam = $demoUser->teams()->where('is_personal', true)->first();

        // Draft project
        $draft = Project::create([
            'user_id' => $demoUser->id,
            'key' => 'demo-draft-' . Str::random(6),
            'label' => 'Getting Started Guide',
            'description' => 'A beginner-friendly guide to help new users get started.',
            'current_step' => ProjectStep::Intro->value,
            'responses' => [],
        ]);
        $personalTeam->projects()->attach($draft->id);

        // In Progress project — must update status after create due to boot event
        $inProgress = Project::create([
            'user_id' => $demoUser->id,
            'key' => 'demo-progress-' . Str::random(6),
            'label' => 'Vancouver Community Survey',
            'description' => 'Gathering feedback from the Vancouver community.',
            'current_step' => ProjectStep::SectionB->value,
            'responses' => [
                'intro_1' => 'Community engagement project',
                'intro_2' => 'Gathering neighbourhood feedback',
                'intro_3' => 'Vancouver residents',
                'section_a_1' => 'Parks and recreation',
                'section_a_2' => 'Public transit improvements',
                'section_a_3' => 'Community safety',
                'section_a_4' => 'Local business support',
                'section_a_5' => 'Environmental sustainability',
                'section_a_6' => 'Housing affordability',
            ],
        ]);
        $inProgress->update(['status' => ProjectStatus::InProgress]);
        $personalTeam->projects()->attach($inProgress->id);

        // Completed project
        $completed = Project::create([
            'user_id' => $demoUser->id,
            'key' => 'demo-complete-' . Str::random(6),
            'label' => 'Annual Team Review',
            'description' => 'End-of-year team performance review and retrospective.',
            'current_step' => ProjectStep::Complete->value,
            'responses' => [
                'intro_1' => 'Annual performance review',
                'intro_2' => 'Evaluating team goals and milestones',
                'intro_3' => 'All team members',
                'section_a_1' => 'Goals met on schedule',
                'section_a_2' => 'Improved code review process',
                'section_a_3' => 'Better cross-team communication',
                'section_a_4' => 'Reduced deployment incidents',
                'section_a_5' => 'Mentorship programme launched',
                'section_a_6' => 'Documentation overhaul completed',
                'section_b_1' => 'Expand to two new markets',
                'section_b_2' => 'Reduce onboarding time by 30%',
                'section_b_3' => 'Launch customer feedback portal',
                'section_b_4' => 'Automate regression testing',
                'section_b_5' => 'Improve uptime to 99.9%',
                'section_b_6' => 'Hire three senior engineers',
                'section_b_7' => 'Establish design system',
                'section_b_8' => 'Migrate to container orchestration',
                'section_b_9' => 'Quarterly team retrospectives',
                'section_c_1' => 'Strong collaboration across teams',
                'section_c_2' => 'Consistent delivery cadence',
                'section_c_3' => 'Proactive incident response',
                'section_c_4' => 'Knowledge sharing sessions',
                'section_c_5' => 'Improved developer experience',
                'section_c_6' => 'Budget stayed within 5% of plan',
                'section_c_7' => 'Customer satisfaction up 15%',
                'section_c_8' => 'Technical debt reduced by 20%',
                'section_c_9' => 'Overall rating: exceeds expectations',
            ],
        ]);
        $completed->update(['status' => ProjectStatus::Completed]);
        $personalTeam->projects()->attach($completed->id);
    }

    /**
     * Create a shared non-personal team with demo user and dummy users.
     */
    private function seedSharedTeam(User $demoUser, Collection $dummyUsers): void
    {
        if ($dummyUsers->count() < 3) {
            $this->command->warn('Not enough dummy users for shared team — skipping.');

            return;
        }

        $team = Team::create([
            'label' => 'Demo Collaboration Team',
            'status' => TeamStatus::ACTIVE,
            'is_personal' => false,
        ]);

        // Dummy user #0 as Owner
        $team->users()->attach($dummyUsers[0]->id);
        $team->assignTeamRole($dummyUsers[0], TeamRole::Owner);

        // Dummy user #1 as Admin
        $team->users()->attach($dummyUsers[1]->id);
        $team->assignTeamRole($dummyUsers[1], TeamRole::Admin);

        // Demo user as Member
        $team->users()->attach($demoUser->id);
        $team->assignTeamRole($demoUser, TeamRole::Member);

        // Dummy user #2 as Member
        $team->users()->attach($dummyUsers[2]->id);
        $team->assignTeamRole($dummyUsers[2], TeamRole::Member);
    }

    /**
     * Create a pending team invitation for the demo user.
     */
    private function seedPendingInvitation(User $demoUser, Collection $dummyUsers): void
    {
        if ($dummyUsers->count() < 4) {
            $this->command->warn('Not enough dummy users for invitation — skipping.');

            return;
        }

        $inviter = $dummyUsers[3];

        // Create a non-personal team owned by dummy user #3
        $team = Team::create([
            'label' => $inviter->name . "'s Project Team",
            'status' => TeamStatus::ACTIVE,
            'is_personal' => false,
        ]);
        $team->users()->attach($inviter->id);
        $team->assignTeamRole($inviter, TeamRole::Owner);

        TeamInvitation::create([
            'team_id' => $team->id,
            'invited_by' => $inviter->id,
            'user_id' => $demoUser->id,
            'email' => $demoUser->email,
            'token' => Str::random(32),
            'role' => TeamRole::Admin->value,
            'status' => InvitationStatus::Pending,
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Create demo notifications for the demo user.
     */
    private function seedNotifications(User $demoUser, Collection $dummyUsers): void
    {
        $sender = $dummyUsers->first();

        // Read notification: story completed
        Notification::create([
            'recipient_id' => $demoUser->id,
            'sender_id' => $sender?->id,
            'title' => 'Story form completed',
            'content' => 'Your "Annual Team Review" story form has been completed successfully.',
            'type' => 'story_completed',
            'link' => '/dashboard',
            'read_at' => now()->subHour(),
        ]);

        // Unread notification: team invitation
        Notification::create([
            'recipient_id' => $demoUser->id,
            'sender_id' => $sender?->id,
            'title' => 'Team invitation received',
            'content' => 'You have been invited to join a new team. Check your invitations to respond.',
            'type' => 'team_invitation',
            'link' => '/invitations',
            'read_at' => null,
        ]);

        // Unread notification: welcome (system notification, sender is demo user)
        Notification::create([
            'recipient_id' => $demoUser->id,
            'sender_id' => $demoUser->id,
            'title' => 'Welcome to the demo',
            'content' => 'Explore the dashboard, manage projects, and collaborate with your team.',
            'type' => 'general',
            'link' => '/dashboard',
            'read_at' => null,
        ]);
    }
}
