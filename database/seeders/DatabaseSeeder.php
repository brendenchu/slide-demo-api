<?php

namespace Database\Seeders;

use App\Enums\Account\TeamStatus;
use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Enums\Story\ProjectStatus;
use App\Enums\Story\ProjectStep;
use App\Models\Account\Plan;
use App\Models\Account\Subscription;
use App\Models\Account\Team;
use App\Models\Story\Project;
use App\Models\Story\Token;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Roles
        foreach (RoleEnum::getInstances() as $role) {
            Role::create([
                'name' => $role,
            ]);
        }

        // Create Permissions
        foreach (PermissionEnum::getInstances() as $permission) {
            Permission::create([
                'name' => $permission,
            ]);
        }

        // Assign Permissions to Roles
        $role = Role::findByName(RoleEnum::SuperAdmin->value);
        $role->givePermissionTo(PermissionEnum::getInstances());

        $role = Role::findByName(RoleEnum::Admin->value);
        $role->givePermissionTo([
            PermissionEnum::ViewAnyUser,
            PermissionEnum::ViewUser,
            PermissionEnum::CreateUser,
            PermissionEnum::UpdateUser,
            PermissionEnum::DeleteUser,
            PermissionEnum::ViewAnyTeam,
            PermissionEnum::ViewTeam,
            PermissionEnum::CreateTeam,
            PermissionEnum::UpdateTeam,
            PermissionEnum::DeleteTeam,
            PermissionEnum::ViewAnyProject,
            PermissionEnum::ViewProject,
            PermissionEnum::CreateProject,
            PermissionEnum::UpdateProject,
            PermissionEnum::DeleteProject,
        ]);

        $role = Role::findByName(RoleEnum::Consultant->value);
        $role->givePermissionTo([
            PermissionEnum::ViewAnyUser,
            PermissionEnum::ViewUser,
            PermissionEnum::ViewAnyTeam,
            PermissionEnum::ViewTeam,
            PermissionEnum::ViewAnyProject,
            PermissionEnum::ViewProject,
            PermissionEnum::CreateProject,
            PermissionEnum::UpdateProject,
            PermissionEnum::DeleteProject,
        ]);

        $role = Role::findByName(RoleEnum::Client->value);
        $role->givePermissionTo([
            PermissionEnum::ViewProject,
            PermissionEnum::CreateProject,
            PermissionEnum::UpdateProject,
        ]);

        $role = Role::findByName(RoleEnum::Guest->value);
        $role->givePermissionTo([
            PermissionEnum::ViewProject,
            PermissionEnum::CreateProject,
        ]);

        // Create essential users
        $this->createEssentialUsers();

        // Create sample data for testing
        $this->createSampleData();
    }

    /**
     * Create essential users (super admin and guest).
     */
    private function createEssentialUsers(): void
    {
        // Create Super Admin
        $superAdmin = User::create([
            'name' => config('demo.super_admin_name'),
            'email' => config('demo.super_admin_email'),
            'password' => bcrypt(config('demo.super_admin_password')),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        $superAdmin->assignRole(RoleEnum::SuperAdmin);

        // Set the auto-created team as current team
        $team = $superAdmin->teams()->first();
        if ($team) {
            $superAdmin->setSetting('current_team', $team->key);
        }

        // Create Guest User
        $guest = User::create([
            'name' => config('demo.guest_name'),
            'email' => config('demo.guest_email'),
            'password' => bcrypt(config('demo.guest_password')),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        $guest->assignRole(RoleEnum::Guest);

        // Set the auto-created team as current team
        $team = $guest->teams()->first();
        if ($team) {
            $guest->setSetting('current_team', $team->key);
        }
    }

    /**
     * Create comprehensive sample data with proper relationships.
     */
    private function createSampleData(): void
    {
        // Create Plans with different configurations
        $monthlyPlan = Plan::factory()->create([
            'name' => 'Monthly Pro Plan',
            'slug' => 'monthly-pro',
            'price' => 29.99,
            'interval' => 'monthly',
            'trial_period' => 14,
            'is_active' => true,
        ]);

        $yearlyPlan = Plan::factory()->create([
            'name' => 'Yearly Business Plan',
            'slug' => 'yearly-business',
            'price' => 299.99,
            'interval' => 'yearly',
            'trial_period' => 30,
            'is_active' => true,
        ]);

        $inactivePlan = Plan::factory()->inactive()->create([
            'name' => 'Legacy Plan',
            'slug' => 'legacy-plan',
            'price' => 19.99,
            'interval' => 'monthly',
            'trial_period' => 0,
        ]);

        // Create Users with different roles
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole(RoleEnum::Admin->value);

        $consultant = User::factory()->create([
            'name' => 'Consultant User',
            'email' => 'consultant@example.com',
        ]);
        $consultant->assignRole(RoleEnum::Consultant->value);

        $client1 = User::factory()->create([
            'name' => 'Client One',
            'email' => 'client1@example.com',
        ]);
        $client1->assignRole(RoleEnum::Client->value);

        $client2 = User::factory()->create([
            'name' => 'Client Two',
            'email' => 'client2@example.com',
        ]);
        $client2->assignRole(RoleEnum::Client->value);

        $client3 = User::factory()->create([
            'name' => 'Client Three',
            'email' => 'client3@example.com',
        ]);
        $client3->assignRole(RoleEnum::Client->value);

        // Create additional teams for collaboration
        $collaborationTeam = Team::factory()->create([
            'key' => 'collaboration-team',
            'label' => 'Collaboration Team',
            'status' => TeamStatus::ACTIVE,
        ]);

        $archivedTeam = Team::factory()->create([
            'key' => 'archived-team',
            'label' => 'Archived Team',
            'status' => TeamStatus::INACTIVE,
        ]);

        // Associate users with additional teams
        $client1->teams()->attach($collaborationTeam, ['is_admin' => true]);
        $client2->teams()->attach($collaborationTeam, ['is_admin' => false]);
        $consultant->teams()->attach($collaborationTeam, ['is_admin' => false]);

        $client3->teams()->attach($archivedTeam, ['is_admin' => true]);

        // Get super admin from database for consistency
        $superAdmin = User::where('email', config('demo.super_admin_email'))->first();

        // Set current teams for users
        foreach ([$superAdmin, $admin, $consultant, $client1, $client2, $client3] as $user) {
            $team = $user->teams()->first();
            if ($team) {
                $user->setSetting('current_team', $team->key);
            }
        }

        // Create Subscriptions with different statuses
        // Active subscription on trial
        Subscription::factory()->onTrial()->create([
            'accountable_type' => User::class,
            'accountable_id' => $client1->id,
            'plan_id' => $monthlyPlan->id,
        ]);

        // Active subscription (no trial)
        Subscription::factory()->create([
            'accountable_type' => Team::class,
            'accountable_id' => $collaborationTeam->id,
            'plan_id' => $yearlyPlan->id,
        ]);

        // Canceled subscription
        Subscription::factory()->canceled()->create([
            'accountable_type' => User::class,
            'accountable_id' => $client2->id,
            'plan_id' => $monthlyPlan->id,
            'canceled_at' => now()->subDays(5),
            'canceled_by' => $client2->id,
            'cancellation_reason' => 'Switching to yearly plan',
        ]);

        // Expired subscription
        Subscription::factory()->expired()->create([
            'accountable_type' => User::class,
            'accountable_id' => $client3->id,
            'plan_id' => $inactivePlan->id,
        ]);

        // Inactive subscription
        Subscription::factory()->inactive()->create([
            'accountable_type' => Team::class,
            'accountable_id' => $archivedTeam->id,
            'plan_id' => $monthlyPlan->id,
        ]);

        // Create Projects with different statuses (no pre-populated responses)
        // 1. Draft project (client1) - user will add their own responses
        $draftProject = Project::factory()->create([
            'label' => 'My Draft Project',
            'description' => 'This is a work in progress project',
        ]);
        $draftProject->status = ProjectStatus::DRAFT;
        $draftProject->save();
        $draftProject->teams()->attach($client1->teams()->first());

        // Create token for draft project (no responses - user will add their own)
        Token::factory()->create([
            'user_id' => $client1->id,
            'project_id' => $draftProject->id,
            'settings' => [
                'last_position' => [
                    'step' => ProjectStep::STEP_ZERO->value,
                    'page' => 1,
                ],
            ],
        ]);

        // 2. Published project (client1) - user will submit their own responses
        $publishedProject1 = Project::factory()->create([
            'label' => 'Completed Project Alpha',
            'description' => 'A fully completed and published project',
        ]);
        $publishedProject1->status = ProjectStatus::PUBLISHED;
        $publishedProject1->save();
        $publishedProject1->teams()->attach($client1->teams()->first());

        // Token will be created when user submits the project
        // No pre-populated responses - users create their own

        // 3. Collaboration project - multiple users can contribute
        $collaborationProject = Project::factory()->create([
            'label' => 'Team Collaboration Project',
            'description' => 'A project worked on by multiple team members',
        ]);
        $collaborationProject->status = ProjectStatus::PUBLISHED;
        $collaborationProject->save();
        $collaborationProject->teams()->attach($collaborationTeam);

        // Tokens will be created when users access the project
        // No pre-populated responses - users create their own

        // 4. Published project (client2)
        $expiredTokenProject = Project::factory()->create([
            'label' => 'Older Published Project',
            'description' => 'Published project with expired token',
        ]);
        $expiredTokenProject->status = ProjectStatus::PUBLISHED;
        $expiredTokenProject->save();
        $expiredTokenProject->teams()->attach($client2->teams()->first());

        // Token will be created when user accesses the project
        // No pre-populated responses - users create their own

        // 5. Archived project (client3)
        $archivedProject = Project::factory()->create([
            'label' => 'Archived Historical Project',
            'description' => 'An old project that has been archived',
        ]);
        $archivedProject->status = ProjectStatus::ARCHIVED;
        $archivedProject->save();
        $archivedProject->teams()->attach($client3->teams()->first());

        // Token will be created when user accesses the project
        // No pre-populated responses - users create their own

        // 6. Multiple draft projects for client3 - user will add their own responses
        for ($i = 1; $i <= 3; $i++) {
            $draft = Project::factory()->create([
                'label' => "Draft Project {$i}",
                'description' => 'Another draft project in various stages',
            ]);
            $draft->status = ProjectStatus::DRAFT;
            $draft->save();
            $draft->teams()->attach($client3->teams()->first());

            // Token will be created when user starts working on the project
            // No pre-populated responses - users create their own
        }
    }
}
