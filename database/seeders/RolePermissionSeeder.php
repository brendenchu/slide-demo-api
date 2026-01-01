<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\ProjectStatus;
use App\Enums\ProjectStep;
use App\Enums\Role as RoleEnum;
use App\Enums\TeamStatus;
use App\Models\Account\Plan;
use App\Models\Account\Subscription;
use App\Models\Account\Team;
use App\Models\Story\Project;
use App\Models\Story\Response;
use App\Models\Story\Token;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
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

        // Create guest user for demo/testing
        $this->createGuestUser();

        // Create sample data
        $this->createSampleData();
    }

    /**
     * Create a guest user with guest credentials.
     */
    private function createGuestUser(): void
    {
        $guest = User::factory()->create([
            'name' => config('demo.guest_name'),
            'email' => config('demo.guest_email'),
            'password' => bcrypt(config('demo.guest_password')),
        ]);

        $guest->assignRole(RoleEnum::Guest->value);

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
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
        ]);
        $superAdmin->assignRole(RoleEnum::SuperAdmin->value);

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

        // Create Projects with different statuses
        // 1. Draft project with in-progress work (client1)
        $draftProject = Project::factory()->create([
            'label' => 'My Draft Project',
            'description' => 'This is a work in progress project',
        ]);
        $draftProject->status = ProjectStatus::DRAFT;
        $draftProject->save();
        $draftProject->teams()->attach($client1->teams()->first());

        // Create token and partial responses for draft project
        Token::factory()->create([
            'user_id' => $client1->id,
            'project_id' => $draftProject->id,
            'settings' => [
                'last_position' => [
                    'step' => ProjectStep::STEP_ONE->value,
                    'page' => 1,
                ],
            ],
        ]);

        // Add partial responses
        $this->createResponses($draftProject, ProjectStep::STEP_ZERO, [
            'intro_1' => 'Sample introduction text',
            'intro_2' => 'More introduction details',
            'intro_3' => 'Final intro paragraph',
        ]);

        // 2. Published project with complete data (client1)
        $publishedProject1 = Project::factory()->create([
            'label' => 'Completed Project Alpha',
            'description' => 'A fully completed and published project',
        ]);
        $publishedProject1->status = ProjectStatus::PUBLISHED;
        $publishedProject1->save();
        $publishedProject1->teams()->attach($client1->teams()->first());

        Token::factory()->create([
            'user_id' => $client1->id,
            'project_id' => $publishedProject1->id,
            'settings' => [
                'last_position' => [
                    'step' => 'complete',
                    'page' => 1,
                ],
            ],
        ]);

        // Add complete responses
        $this->createCompleteResponses($publishedProject1);

        // 3. Published project on collaboration team (multiple users)
        $collaborationProject = Project::factory()->create([
            'label' => 'Team Collaboration Project',
            'description' => 'A project worked on by multiple team members',
        ]);
        $collaborationProject->status = ProjectStatus::PUBLISHED;
        $collaborationProject->save();
        $collaborationProject->teams()->attach($collaborationTeam);

        // Create tokens for multiple users on same project
        Token::factory()->create([
            'user_id' => $client1->id,
            'project_id' => $collaborationProject->id,
            'settings' => ['last_position' => ['step' => 'complete', 'page' => 1]],
        ]);

        Token::factory()->create([
            'user_id' => $client2->id,
            'project_id' => $collaborationProject->id,
            'settings' => ['last_position' => ['step' => 'complete', 'page' => 1]],
        ]);

        $this->createCompleteResponses($collaborationProject);

        // 4. Published project with expired token (client2)
        $expiredTokenProject = Project::factory()->create([
            'label' => 'Older Published Project',
            'description' => 'Published project with expired token',
        ]);
        $expiredTokenProject->status = ProjectStatus::PUBLISHED;
        $expiredTokenProject->save();
        $expiredTokenProject->teams()->attach($client2->teams()->first());

        Token::factory()->expired()->create([
            'user_id' => $client2->id,
            'project_id' => $expiredTokenProject->id,
            'settings' => ['last_position' => ['step' => 'complete', 'page' => 1]],
        ]);

        $this->createCompleteResponses($expiredTokenProject);

        // 5. Archived project (client3)
        $archivedProject = Project::factory()->create([
            'label' => 'Archived Historical Project',
            'description' => 'An old project that has been archived',
        ]);
        $archivedProject->status = ProjectStatus::ARCHIVED;
        $archivedProject->save();
        $archivedProject->teams()->attach($client3->teams()->first());

        Token::factory()->revoked()->create([
            'user_id' => $client3->id,
            'project_id' => $archivedProject->id,
            'settings' => ['last_position' => ['step' => 'complete', 'page' => 1]],
        ]);

        $this->createCompleteResponses($archivedProject);

        // 6. Multiple draft projects for client3
        for ($i = 1; $i <= 3; $i++) {
            $draft = Project::factory()->create([
                'label' => "Draft Project {$i}",
                'description' => 'Another draft project in various stages',
            ]);
            $draft->status = ProjectStatus::DRAFT;
            $draft->save();
            $draft->teams()->attach($client3->teams()->first());

            $token = Token::factory()->create([
                'user_id' => $client3->id,
                'project_id' => $draft->id,
                'settings' => [
                    'last_position' => [
                        'step' => fake()->randomElement([
                            ProjectStep::STEP_ZERO->value,
                            ProjectStep::STEP_ONE->value,
                            ProjectStep::STEP_TWO->value,
                        ]),
                        'page' => 1,
                    ],
                ],
            ]);

            // Add random partial responses
            if (fake()->boolean(70)) {
                $this->createResponses($draft, ProjectStep::STEP_ZERO, [
                    'intro_1' => fake()->sentence(),
                    'intro_2' => fake()->sentence(),
                    'intro_3' => fake()->sentence(),
                ]);
            }
        }
    }

    /**
     * Create responses for a specific step.
     */
    private function createResponses(Project $project, ProjectStep $step, array $responses): void
    {
        foreach ($responses as $key => $value) {
            Response::create([
                'project_id' => $project->id,
                'step' => $step->value,
                'key' => $key,
                'value' => $value,
            ]);
        }
    }

    /**
     * Create complete responses for all steps of a project.
     */
    private function createCompleteResponses(Project $project): void
    {
        // Intro step responses
        $this->createResponses($project, ProjectStep::STEP_ZERO, [
            'intro_1' => 'Comprehensive introduction to the project scope and objectives',
            'intro_2' => 'Detailed background information and context',
            'intro_3' => 'Key stakeholders and their involvement',
        ]);

        // Section A responses (booleans and conditionals)
        $this->createResponses($project, ProjectStep::STEP_ONE, [
            'section_a_1' => '0',
            'section_a_2' => '0',
            'section_a_3' => '0',
        ]);

        // Section B responses
        $this->createResponses($project, ProjectStep::STEP_TWO, [
            'section_b_1' => fake()->sentence(),
            'section_b_2' => fake()->sentence(),
            'section_b_3' => fake()->paragraph(),
            'section_b_4' => fake()->numberBetween(1, 100),
            'section_b_5' => fake()->sentence(),
            'section_b_6' => fake()->sentence(),
            'section_b_7' => fake()->paragraph(),
            'section_b_8' => fake()->sentence(),
            'section_b_9' => fake()->sentence(),
        ]);

        // Section C responses
        $this->createResponses($project, ProjectStep::STEP_THREE, [
            'section_c_1' => fake()->sentence(),
            'section_c_2' => fake()->sentence(),
            'section_c_3' => fake()->paragraph(),
            'section_c_4' => fake()->sentence(),
            'section_c_5' => fake()->sentence(),
            'section_c_6' => fake()->paragraph(),
            'section_c_7' => fake()->sentence(),
            'section_c_8' => fake()->sentence(),
            'section_c_9' => fake()->paragraph(3),
        ]);
    }
}
