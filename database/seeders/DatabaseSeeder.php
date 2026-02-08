<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
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
        $this->createRolesAndPermissions();
        $this->createDemoUsers();
        $this->createDummyUsers();
    }

    /**
     * Create roles and assign permissions.
     */
    private function createRolesAndPermissions(): void
    {
        foreach (RoleEnum::getInstances() as $role) {
            Role::create(['name' => $role]);
        }

        foreach (PermissionEnum::getInstances() as $permission) {
            Permission::create(['name' => $permission]);
        }

        Role::findByName(RoleEnum::SuperAdmin->value)
            ->givePermissionTo(PermissionEnum::getInstances());

        Role::findByName(RoleEnum::Admin->value)->givePermissionTo([
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

        Role::findByName(RoleEnum::Consultant->value)->givePermissionTo([
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

        Role::findByName(RoleEnum::Client->value)->givePermissionTo([
            PermissionEnum::ViewProject,
            PermissionEnum::CreateProject,
            PermissionEnum::UpdateProject,
        ]);

        Role::findByName(RoleEnum::Guest->value)->givePermissionTo([
            PermissionEnum::ViewProject,
            PermissionEnum::CreateProject,
        ]);
    }

    /**
     * Create demo credential users with full profile details.
     */
    private function createDemoUsers(): void
    {
        $demoUsers = [
            [
                'name' => config('demo.super_admin_name'),
                'email' => config('demo.super_admin_email'),
                'password' => config('demo.super_admin_password'),
                'role' => RoleEnum::SuperAdmin,
            ],
            [
                'name' => config('demo.admin_name'),
                'email' => config('demo.admin_email'),
                'password' => config('demo.admin_password'),
                'role' => RoleEnum::Admin,
            ],
            [
                'name' => config('demo.consultant_name'),
                'email' => config('demo.consultant_email'),
                'password' => config('demo.consultant_password'),
                'role' => RoleEnum::Consultant,
            ],
            [
                'name' => config('demo.client_name'),
                'email' => config('demo.client_email'),
                'password' => config('demo.client_password'),
                'role' => RoleEnum::Client,
            ],
            [
                'name' => config('demo.guest_name'),
                'email' => config('demo.guest_email'),
                'password' => config('demo.guest_password'),
                'role' => RoleEnum::Guest,
            ],
        ];

        foreach ($demoUsers as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            $user->assignRole($data['role']);
            $this->fillProfile($user);
        }
    }

    /**
     * Create 10 dummy users with the Client role and full profile details.
     */
    private function createDummyUsers(): void
    {
        $users = User::factory()->count(10)->create();

        foreach ($users as $user) {
            $user->assignRole(RoleEnum::Client);
            $this->fillProfile($user);
        }
    }

    /**
     * Fill a user's auto-created profile with full details.
     */
    private function fillProfile(User $user): void
    {
        $user->profile->update([
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'zip' => fake()->postcode(),
            'country' => fake()->country(),
            'timezone' => fake()->timezone(),
            'locale' => fake()->locale(),
            'currency' => fake()->currencyCode(),
        ]);
    }
}
